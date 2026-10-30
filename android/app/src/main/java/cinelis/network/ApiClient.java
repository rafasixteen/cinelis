package cinelis.network;

import com.android.volley.NetworkResponse;
import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.JsonArrayRequest;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.StringRequest;

import org.json.JSONArray;
import org.json.JSONObject;

import java.net.URLEncoder;
import java.nio.charset.StandardCharsets;
import java.util.Collections;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import cinelis.model.CinemaSession;
import cinelis.model.Movie;
import cinelis.model.Order;
import cinelis.model.Seat;
import cinelis.model.Ticket;
import cinelis.model.User;
import cinelis.storage.PreferencesManager;

public final class ApiClient {
    private static ApiClient instance;
    private final RequestQueue queue;
    private final PreferencesManager preferences;
    private String bearerToken;

    private ApiClient(RequestQueue queue, PreferencesManager preferences) {
        this.queue = queue;
        this.preferences = preferences;
    }

    public static synchronized void initialize(RequestQueue queue, PreferencesManager preferences) {
        instance = new ApiClient(queue, preferences);
    }

    public static synchronized void initialize(RequestQueue queue, PreferencesManager preferences,
                                               String initialToken) {
        initialize(queue, preferences);
        instance.bearerToken = initialToken;
    }

    public static synchronized ApiClient get() {
        if (instance == null) throw new IllegalStateException("ApiClient has not been initialized");
        return instance;
    }

    public boolean isAuthenticated() { return bearerToken != null; }

    public void clearAuthentication() { bearerToken = null; }

    public void login(String email, String password, Object tag, ApiCallback<User> callback) {
        JSONObject body = MockApiStore.object("email", email, "password", password);
        object(Request.Method.POST, "/auth/login", body, tag, json -> {
            bearerToken = json.optString("token", null);
            return JsonModelMapper.user(json.optJSONObject("user"));
        }, callback);
    }

    public void logout(Object tag, ApiCallback<Void> callback) {
        empty(Request.Method.POST, "/auth/logout", tag, new ApiCallback<Void>() {
            @Override public void onSuccess(Void value) { bearerToken = null; callback.onSuccess(null); }
            @Override public void onError(ApiError error) { callback.onError(error); }
        });
    }

    public void register(String name, String email, String password, Object tag, ApiCallback<User> callback) {
        object(Request.Method.POST, "/auth/register",
                MockApiStore.object("name", name, "email", email, "password", password), tag,
                JsonModelMapper::user, callback);
    }

    public void getCurrentUser(Object tag, ApiCallback<User> callback) {
        object(Request.Method.GET, "/auth/me", null, tag, JsonModelMapper::user, callback);
    }

    public void updateCurrentUser(String name, String email, Object tag, ApiCallback<User> callback) {
        object(Request.Method.PUT, "/auth/me", MockApiStore.object("name", name, "email", email),
                tag, JsonModelMapper::user, callback);
    }

    public void deleteCurrentUser(Object tag, ApiCallback<Void> callback) {
        empty(Request.Method.DELETE, "/auth/me", tag, new ApiCallback<Void>() {
            @Override public void onSuccess(Void value) { bearerToken = null; callback.onSuccess(null); }
            @Override public void onError(ApiError error) { callback.onError(error); }
        });
    }

    public void listMovies(String query, String genre, Object tag, ApiCallback<List<Movie>> callback) {
        String path = "/movies?q=" + encode(query) + "&genre=" + encode(genre);
        object(Request.Method.GET, path, null, tag,
                json -> JsonModelMapper.movies(json.optJSONArray("items")), callback);
    }

    public void getMovie(String movieId, Object tag, ApiCallback<Movie> callback) {
        object(Request.Method.GET, "/movies/" + encode(movieId), null, tag, JsonModelMapper::movie, callback);
    }

    public void listMovieSessions(String movieId, String date, Object tag,
                                  ApiCallback<List<CinemaSession>> callback) {
        array(Request.Method.GET, "/movies/" + encode(movieId) + "/sessions?date=" + encode(date),
                tag, JsonModelMapper::sessions, callback);
    }

    public void getSession(String sessionId, Object tag, ApiCallback<CinemaSession> callback) {
        object(Request.Method.GET, "/sessions/" + encode(sessionId), null, tag,
                JsonModelMapper::session, callback);
    }

    public void getSessionSeats(String sessionId, Object tag, ApiCallback<List<Seat>> callback) {
        object(Request.Method.GET, "/sessions/" + encode(sessionId) + "/seats", null, tag,
                json -> JsonModelMapper.seats(json.optJSONArray("seats")), callback);
    }

    public void createSeatHold(String sessionId, List<String> seatIds, Object tag,
                               ApiCallback<List<Seat>> callback) {
        object(Request.Method.POST, "/sessions/" + encode(sessionId) + "/holds",
                MockApiStore.object("seatIds", new JSONArray(seatIds)), tag,
                json -> JsonModelMapper.seats(json.optJSONArray("seats")), callback);
    }

    public void createOrder(String sessionId, List<String> seatIds, Object tag, ApiCallback<Order> callback) {
        object(Request.Method.POST, "/orders",
                MockApiStore.object("sessionId", sessionId, "seatIds", new JSONArray(seatIds)), tag,
                JsonModelMapper::order, callback);
    }

    public void listOrders(Object tag, ApiCallback<List<Order>> callback) {
        object(Request.Method.GET, "/orders", null, tag,
                json -> JsonModelMapper.orders(json.optJSONArray("items")), callback);
    }

    public void getOrder(String orderId, Object tag, ApiCallback<Order> callback) {
        object(Request.Method.GET, "/orders/" + encode(orderId), null, tag, JsonModelMapper::order, callback);
    }

    public void listTickets(boolean upcoming, Object tag, ApiCallback<List<Ticket>> callback) {
        object(Request.Method.GET, "/tickets?upcoming=" + upcoming, null, tag,
                json -> JsonModelMapper.tickets(json.optJSONArray("items")), callback);
    }

    public void getTicket(String ticketId, Object tag, ApiCallback<Ticket> callback) {
        object(Request.Method.GET, "/tickets/" + encode(ticketId), null, tag,
                JsonModelMapper::ticket, callback);
    }

    public void cancel(Object tag) { queue.cancelAll(tag); }

    private <T> void object(int method, String path, JSONObject body, Object tag,
                            ObjectMapper<T> mapper, ApiCallback<T> callback) {
        JsonObjectRequest request = new JsonObjectRequest(method, url(path), body,
                response -> map(response, mapper, callback), error -> callback.onError(apiError(error))) {
            @Override public Map<String, String> getHeaders() { return authorizationHeaders(); }
        };
        request.setTag(tag);
        queue.add(request);
    }

    private <T> void array(int method, String path, Object tag, ArrayMapper<T> mapper, ApiCallback<T> callback) {
        JsonArrayRequest request = new JsonArrayRequest(method, url(path), null,
                response -> map(response, mapper, callback), error -> callback.onError(apiError(error))) {
            @Override public Map<String, String> getHeaders() { return authorizationHeaders(); }
        };
        request.setTag(tag);
        queue.add(request);
    }

    private void empty(int method, String path, Object tag, ApiCallback<Void> callback) {
        StringRequest request = new StringRequest(method, url(path), response -> callback.onSuccess(null),
                error -> callback.onError(apiError(error))) {
            @Override public Map<String, String> getHeaders() { return authorizationHeaders(); }
        };
        request.setTag(tag);
        queue.add(request);
    }

    private Map<String, String> authorizationHeaders() {
        if (bearerToken == null) return Collections.emptyMap();
        Map<String, String> headers = new HashMap<>();
        headers.put("Authorization", "Bearer " + bearerToken);
        return headers;
    }

    private String url(String path) {
        String base = preferences.getServerUrl().trim().replaceAll("/+$", "");
        return base + (path.startsWith("/") ? path : "/" + path);
    }

    private static String encode(String value) {
        try {
            return URLEncoder.encode(value == null ? "" : value, "UTF-8");
        } catch (java.io.UnsupportedEncodingException exception) {
            throw new IllegalStateException(exception);
        }
    }

    private static <T> void map(JSONObject value, ObjectMapper<T> mapper, ApiCallback<T> callback) {
        try { callback.onSuccess(mapper.map(value)); }
        catch (Exception exception) { callback.onError(new ApiError(0, "mapping_error", exception.getMessage(), null)); }
    }

    private static <T> void map(JSONArray value, ArrayMapper<T> mapper, ApiCallback<T> callback) {
        try { callback.onSuccess(mapper.map(value)); }
        catch (Exception exception) { callback.onError(new ApiError(0, "mapping_error", exception.getMessage(), null)); }
    }

    private static ApiError apiError(VolleyError error) {
        NetworkResponse response = error.networkResponse;
        int status = response == null ? 0 : response.statusCode;
        try {
            JSONObject payload = response == null ? new JSONObject() :
                    new JSONObject(new String(response.data, StandardCharsets.UTF_8));
            return new ApiError(status, payload.optString("code", "network_error"),
                    payload.optString("message", "Não foi possível concluir o pedido."),
                    payload.optJSONObject("fields"));
        } catch (Exception ignored) {
            return new ApiError(status, "network_error", "Não foi possível concluir o pedido.", null);
        }
    }

    private interface ObjectMapper<T> { T map(JSONObject value) throws Exception; }
    private interface ArrayMapper<T> { T map(JSONArray value) throws Exception; }
}
