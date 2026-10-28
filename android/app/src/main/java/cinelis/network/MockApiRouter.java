package cinelis.network;

import com.android.volley.Request;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.net.URI;
import java.net.URLDecoder;
import java.nio.charset.StandardCharsets;
import java.util.ArrayList;
import java.util.Collections;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Locale;
import java.util.Map;

public final class MockApiRouter {
    private final MockApiStore store;

    public MockApiRouter(MockApiStore store) {
        this.store = store;
    }

    public synchronized MockApiResponse route(int method, String url, Map<String, String> headers, byte[] body) {
        try {
            ParsedUrl parsed = ParsedUrl.from(url);
            String path = parsed.path;
            JSONObject payload = parseBody(body);

            if (method == Request.Method.POST && "/auth/login".equals(path)) return login(payload);
            if (method == Request.Method.POST && "/auth/register".equals(path)) return register(payload);
            if (method == Request.Method.POST && "/auth/logout".equals(path)) return logout(headers);
            if ("/auth/me".equals(path)) {
                if (method == Request.Method.GET) return ok(user(headers).json());
                if (method == Request.Method.PUT) return updateUser(headers, payload);
                if (method == Request.Method.DELETE) return deleteUser(headers);
            }
            if (method == Request.Method.GET && "/movies".equals(path)) return listMovies(parsed.query);
            if (method == Request.Method.GET && path.matches("/movies/[^/]+/sessions")) {
                return listSessions(segment(path, 2), parsed.query);
            }
            if (method == Request.Method.GET && path.matches("/movies/[^/]+")) return getMovie(segment(path, 2));
            if (method == Request.Method.GET && path.matches("/sessions/[^/]+/seats")) return seats(segment(path, 2));
            if (method == Request.Method.POST && path.matches("/sessions/[^/]+/holds")) {
                return hold(headers, segment(path, 2), payload);
            }
            if (method == Request.Method.GET && path.matches("/sessions/[^/]+")) return session(segment(path, 2));
            if ("/orders".equals(path)) {
                if (method == Request.Method.GET) return listOrders(headers, parsed.query);
                if (method == Request.Method.POST) return createOrder(headers, payload);
            }
            if (method == Request.Method.GET && path.matches("/orders/[^/]+")) {
                return getOrder(headers, segment(path, 2));
            }
            if (method == Request.Method.GET && "/tickets".equals(path)) return listTickets(headers, parsed.query);
            if (method == Request.Method.GET && path.matches("/tickets/[^/]+")) {
                return getTicket(headers, segment(path, 2));
            }
            throw new MockApiException(404, "not_found", "O recurso solicitado não foi encontrado.");
        } catch (MockApiException exception) {
            return error(exception);
        } catch (Exception exception) {
            return MockApiResponse.json(500, MockApiStore.object("code", "mock_error",
                    "message", "O serviço simulado encontrou um erro.").toString());
        }
    }

    private MockApiResponse login(JSONObject body) {
        require(body, "email", "password");
        String email = body.optString("email").trim().toLowerCase(Locale.ROOT);
        MockApiStore.UserRecord user = store.usersByEmail.get(email);
        if (user == null || !user.password.equals(body.optString("password"))) {
            throw new MockApiException(401, "unauthorized", "Email ou palavra-passe inválidos.");
        }
        String token = store.issueToken(user.id);
        return ok(MockApiStore.object("token", token, "expiresAt", "2026-11-28T23:59:59Z",
                "user", user.json()));
    }

    private MockApiResponse register(JSONObject body) {
        require(body, "name", "email", "password");
        String name = body.optString("name").trim();
        String email = body.optString("email").trim().toLowerCase(Locale.ROOT);
        String password = body.optString("password");
        JSONObject fields = new JSONObject();
        if (name.isEmpty()) MockApiStore.set(fields, "name", MockApiStore.array("Indique o nome."));
        if (!email.contains("@")) MockApiStore.set(fields, "email", MockApiStore.array("Indique um email válido."));
        if (password.length() < 8) MockApiStore.set(fields, "password", MockApiStore.array("Use pelo menos 8 caracteres."));
        if (fields.length() > 0) validation(fields);
        if (store.usersByEmail.containsKey(email)) {
            throw new MockApiException(409, "email_exists", "O email já está registado.");
        }
        MockApiStore.UserRecord user = new MockApiStore.UserRecord(MockApiStore.uuid(1000 + store.usersByEmail.size()),
                name, email, password, MockApiStore.CREATED_AT);
        store.usersByEmail.put(email, user);
        return json(201, user.json());
    }

    private MockApiResponse logout(Map<String, String> headers) {
        String token = bearer(headers);
        user(headers);
        store.tokenUsers.remove(token);
        return MockApiResponse.empty(204);
    }

    private MockApiResponse updateUser(Map<String, String> headers, JSONObject body) {
        MockApiStore.UserRecord user = user(headers);
        require(body, "name", "email");
        String name = body.optString("name").trim();
        String email = body.optString("email").trim().toLowerCase(Locale.ROOT);
        JSONObject fields = new JSONObject();
        if (name.isEmpty()) MockApiStore.set(fields, "name", MockApiStore.array("Indique o nome."));
        if (!email.contains("@")) MockApiStore.set(fields, "email", MockApiStore.array("Indique um email válido."));
        if (fields.length() > 0) validation(fields);
        MockApiStore.UserRecord duplicate = store.usersByEmail.get(email);
        if (duplicate != null && duplicate != user) {
            throw new MockApiException(409, "email_exists", "O email já é utilizado por outra conta.");
        }
        store.usersByEmail.remove(user.email);
        user.name = name;
        user.email = email;
        store.usersByEmail.put(email, user);
        return ok(user.json());
    }

    private MockApiResponse deleteUser(Map<String, String> headers) {
        MockApiStore.UserRecord user = user(headers);
        store.usersByEmail.remove(user.email);
        store.revokeUserTokens(user.id);
        return MockApiResponse.empty(204);
    }

    private MockApiResponse listMovies(Map<String, String> query) {
        String text = query.getOrDefault("q", "").toLowerCase(Locale.ROOT);
        String genre = query.getOrDefault("genre", "").toLowerCase(Locale.ROOT);
        JSONArray items = new JSONArray();
        for (JSONObject movie : store.movies.values()) {
            String title = movie.optString("title").toLowerCase(Locale.ROOT);
            String genres = movie.optJSONArray("genres").toString().toLowerCase(Locale.ROOT);
            if ((text.isEmpty() || title.contains(text)) && (genre.isEmpty() || genres.contains(genre))) {
                items.put(MockApiStore.copy(movie));
            }
        }
        return ok(paged(items, query));
    }

    private MockApiResponse getMovie(String id) {
        JSONObject movie = store.movies.get(id);
        if (movie == null) notFound("Filme não encontrado.");
        return ok(MockApiStore.copy(movie));
    }

    private MockApiResponse listSessions(String movieId, Map<String, String> query) {
        if (!store.movies.containsKey(movieId)) notFound("Filme não encontrado.");
        String date = query.get("date");
        JSONArray result = new JSONArray();
        for (JSONObject session : store.sessions.values()) {
            if (movieId.equals(session.optJSONObject("movie").optString("id"))
                    && (date == null || session.optString("startsAt").startsWith(date))) {
                result.put(MockApiStore.sessionSummary(session));
            }
        }
        return json(200, result);
    }

    private MockApiResponse session(String id) {
        JSONObject value = store.sessions.get(id);
        if (value == null) notFound("Sessão não encontrada.");
        return ok(MockApiStore.copy(value));
    }

    private MockApiResponse seats(String sessionId) {
        if (!store.sessions.containsKey(sessionId)) notFound("Sessão não encontrada.");
        JSONArray seats = new JSONArray();
        JSONArray coordinates = new JSONArray();
        for (JSONObject seat : store.seatsBySession.get(sessionId).values()) {
            seats.put(MockApiStore.copy(seat));
            coordinates.put(MockApiStore.object("seatId", seat.optString("id"),
                    "x", seat.optInt("number") - 1, "y", seat.optString("row").charAt(0) - 'A'));
        }
        JSONObject layout = MockApiStore.object("version", 1,
                "grid", MockApiStore.object("columns", 8, "rows", 5), "seats", coordinates,
                "elements", MockApiStore.array(MockApiStore.object("type", "screen", "x", 0,
                        "y", 0, "width", 8, "height", 1)));
        return ok(MockApiStore.object("sessionId", sessionId, "layout", layout, "seats", seats));
    }

    private MockApiResponse hold(Map<String, String> headers, String sessionId, JSONObject body) {
        user(headers);
        if (!store.sessions.containsKey(sessionId)) notFound("Sessão não encontrada.");
        JSONArray ids = requiredSeatIds(body);
        List<JSONObject> selected = resolveSeats(sessionId, ids);
        for (JSONObject seat : selected) {
            if (!"available".equals(seat.optString("status"))) {
                throw new MockApiException(409, "seat_unavailable", "Um ou mais lugares já não estão disponíveis.");
            }
        }
        JSONArray held = new JSONArray();
        for (JSONObject seat : selected) {
            MockApiStore.set(seat, "status", "held");
            held.put(MockApiStore.seatWithoutStatus(seat));
        }
        return json(201, MockApiStore.object("sessionId", sessionId, "seats", held,
                "expiresAt", "2026-10-28T23:59:59Z"));
    }

    private MockApiResponse createOrder(Map<String, String> headers, JSONObject body) {
        MockApiStore.UserRecord user = user(headers);
        require(body, "sessionId", "seatIds");
        String sessionId = body.optString("sessionId");
        JSONObject session = store.sessions.get(sessionId);
        if (session == null) notFound("Sessão não encontrada.");
        List<JSONObject> selected = resolveSeats(sessionId, requiredSeatIds(body));
        for (JSONObject seat : selected) {
            if (!"held".equals(seat.optString("status"))) {
                throw new MockApiException(409, "hold_expired", "A reserva expirou ou o lugar deixou de estar disponível.");
            }
        }
        String orderId = store.nextOrderId();
        JSONArray tickets = new JSONArray();
        int sequence = 1;
        for (JSONObject seat : selected) {
            MockApiStore.set(seat, "status", "sold");
            String ticketId = store.nextTicketId();
            JSONObject ticket = store.ticket(ticketId, orderId, session, seat,
                    "CIN-" + orderId.substring(0, 8) + "-" + sequence++);
            store.tickets.put(ticketId, ticket);
            store.ticketOwners.put(ticketId, user.id);
            tickets.put(ticket);
        }
        JSONObject order = MockApiStore.object("id", orderId,
                "total", session.optDouble("price") * selected.size(),
                "createdAt", "2026-10-28T12:00:00Z", "tickets", tickets);
        store.orders.put(orderId, order);
        store.orderOwners.put(orderId, user.id);
        return json(201, order);
    }

    private MockApiResponse listOrders(Map<String, String> headers, Map<String, String> query) {
        MockApiStore.UserRecord user = user(headers);
        JSONArray items = new JSONArray();
        for (Map.Entry<String, JSONObject> entry : store.orders.entrySet()) {
            if (user.id.equals(store.orderOwners.get(entry.getKey()))) items.put(MockApiStore.copy(entry.getValue()));
        }
        return ok(paged(items, query));
    }

    private MockApiResponse getOrder(Map<String, String> headers, String id) {
        MockApiStore.UserRecord user = user(headers);
        JSONObject order = store.orders.get(id);
        if (order == null || !user.id.equals(store.orderOwners.get(id))) notFound("Compra não encontrada.");
        return ok(MockApiStore.copy(order));
    }

    private MockApiResponse listTickets(Map<String, String> headers, Map<String, String> query) {
        MockApiStore.UserRecord user = user(headers);
        boolean upcoming = "true".equalsIgnoreCase(query.get("upcoming"));
        JSONArray items = new JSONArray();
        for (Map.Entry<String, JSONObject> entry : store.tickets.entrySet()) {
            JSONObject ticket = entry.getValue();
            if (user.id.equals(store.ticketOwners.get(entry.getKey()))
                    && (!upcoming || ticket.optJSONObject("session").optString("startsAt").compareTo("2026-10-28") >= 0)) {
                items.put(MockApiStore.copy(ticket));
            }
        }
        return ok(paged(items, query));
    }

    private MockApiResponse getTicket(Map<String, String> headers, String id) {
        MockApiStore.UserRecord user = user(headers);
        JSONObject ticket = store.tickets.get(id);
        if (ticket == null || !user.id.equals(store.ticketOwners.get(id))) notFound("Bilhete não encontrado.");
        return ok(MockApiStore.copy(ticket));
    }

    private List<JSONObject> resolveSeats(String sessionId, JSONArray ids) {
        LinkedHashMap<String, JSONObject> seats = store.seatsBySession.get(sessionId);
        List<JSONObject> selected = new ArrayList<>();
        for (int index = 0; index < ids.length(); index++) {
            JSONObject seat = seats.get(ids.optString(index));
            if (seat == null) validation(MockApiStore.object("seatIds", MockApiStore.array("Lugar inválido.")));
            selected.add(seat);
        }
        return selected;
    }

    private JSONArray requiredSeatIds(JSONObject body) {
        JSONArray ids = body.optJSONArray("seatIds");
        if (ids == null || ids.length() == 0) {
            validation(MockApiStore.object("seatIds", MockApiStore.array("Selecione pelo menos um lugar.")));
        }
        return ids;
    }

    private MockApiStore.UserRecord user(Map<String, String> headers) {
        String id = store.tokenUsers.get(bearer(headers));
        MockApiStore.UserRecord user = id == null ? null : store.findUserById(id);
        if (user == null) throw new MockApiException(401, "unauthorized", "Token inexistente, inválido ou expirado.");
        return user;
    }

    private static String bearer(Map<String, String> headers) {
        String value = headers == null ? null : headers.get("Authorization");
        if (value == null && headers != null) value = headers.get("authorization");
        if (value == null || !value.startsWith("Bearer ")) {
            throw new MockApiException(401, "unauthorized", "Token inexistente, inválido ou expirado.");
        }
        return value.substring(7);
    }

    private static void require(JSONObject body, String... keys) {
        JSONObject fields = new JSONObject();
        for (String key : keys) {
            if (!body.has(key) || body.isNull(key) || body.optString(key).trim().isEmpty()) {
                MockApiStore.set(fields, key, MockApiStore.array("Campo obrigatório."));
            }
        }
        if (fields.length() > 0) validation(fields);
    }

    private static void validation(JSONObject fields) {
        throw new MockApiException(422, "validation_error", "Os dados fornecidos não são válidos.", fields);
    }

    private static void notFound(String message) {
        throw new MockApiException(404, "not_found", message);
    }

    private static MockApiResponse error(MockApiException exception) {
        JSONObject payload = MockApiStore.object("code", exception.getCode(), "message", exception.getMessage());
        if (exception.getFields() != null) MockApiStore.set(payload, "fields", exception.getFields());
        return json(exception.getStatusCode(), payload);
    }

    private static MockApiResponse ok(JSONObject body) { return json(200, body); }
    private static MockApiResponse json(int status, JSONObject body) { return MockApiResponse.json(status, body.toString()); }
    private static MockApiResponse json(int status, JSONArray body) { return MockApiResponse.json(status, body.toString()); }

    private static JSONObject paged(JSONArray items, Map<String, String> query) {
        int page = positive(query.get("page"), 1);
        int perPage = Math.min(100, positive(query.get("perPage"), 20));
        int from = Math.min(items.length(), (page - 1) * perPage);
        int to = Math.min(items.length(), from + perPage);
        JSONArray slice = new JSONArray();
        for (int index = from; index < to; index++) slice.put(items.opt(index));
        return MockApiStore.object("items", slice, "pagination",
                MockApiStore.object("page", page, "perPage", perPage, "total", items.length()));
    }

    private static int positive(String value, int fallback) {
        try { return Math.max(1, Integer.parseInt(value)); }
        catch (Exception ignored) { return fallback; }
    }

    private static JSONObject parseBody(byte[] body) {
        if (body == null || body.length == 0) return new JSONObject();
        try { return new JSONObject(new String(body, StandardCharsets.UTF_8)); }
        catch (JSONException exception) {
            throw new MockApiException(422, "validation_error", "O corpo JSON não é válido.",
                    MockApiStore.object("body", MockApiStore.array("JSON inválido.")));
        }
    }

    private static String segment(String path, int index) {
        return path.substring(1).split("/")[index - 1];
    }

    private static final class ParsedUrl {
        final String path;
        final Map<String, String> query;

        ParsedUrl(String path, Map<String, String> query) {
            this.path = path;
            this.query = query;
        }

        static ParsedUrl from(String value) {
            try {
                String candidate = value == null || value.isBlank() ? "/" : value.trim();
                URI uri = candidate.contains("://") ? URI.create(candidate) : URI.create("mock://local" +
                        (candidate.startsWith("/") ? candidate : "/" + candidate));
                String path = decode(uri.getRawPath()).replaceAll("/{2,}", "/");
                int version = path.indexOf("/v1/");
                if (version >= 0) path = path.substring(version + 3);
                else if ("/v1".equals(path)) path = "/";
                if (path.length() > 1 && path.endsWith("/")) path = path.substring(0, path.length() - 1);
                Map<String, String> query = new LinkedHashMap<>();
                String rawQuery = uri.getRawQuery();
                if (rawQuery != null) {
                    for (String pair : rawQuery.split("&")) {
                        String[] parts = pair.split("=", 2);
                        query.put(decode(parts[0]), parts.length == 2 ? decode(parts[1]) : "");
                    }
                }
                return new ParsedUrl(path, query);
            } catch (Exception exception) {
                throw new MockApiException(404, "not_found", "URL simulada inválida.");
            }
        }

        private static String decode(String value) {
            return URLDecoder.decode(value == null ? "" : value, StandardCharsets.UTF_8);
        }
    }
}
