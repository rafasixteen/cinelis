package cinelis.network;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.Iterator;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;

public final class MockApiStore {
    static final String ANA_USER_ID = "00000001-0000-4000-8000-000000000001";
    static final String CREATED_AT = "2026-10-01T10:00:00Z";

    final Map<String, JSONObject> movies = new LinkedHashMap<>();
    final Map<String, JSONObject> sessions = new LinkedHashMap<>();
    final Map<String, LinkedHashMap<String, JSONObject>> seatsBySession = new LinkedHashMap<>();
    final Map<String, UserRecord> usersByEmail = new LinkedHashMap<>();
    final Map<String, String> tokenUsers = new LinkedHashMap<>();
    final Map<String, JSONObject> orders = new LinkedHashMap<>();
    final Map<String, JSONObject> tickets = new LinkedHashMap<>();
    final Map<String, String> orderOwners = new LinkedHashMap<>();
    final Map<String, String> ticketOwners = new LinkedHashMap<>();
    private int tokenSequence = 1;
    private int orderSequence = 100;
    private int ticketSequence = 100;

    public MockApiStore() {
        seedMovies();
        seedSessionsAndSeats();
        usersByEmail.put("ana@exemplo.pt", new UserRecord(ANA_USER_ID, "Ana Martins",
                "ana@exemplo.pt", "password123", CREATED_AT));
        seedOrdersAndTickets();
    }

    String issueToken(String userId) {
        String token = "cinelis-mock-token-" + tokenSequence++ + "-" + userId;
        tokenUsers.put(token, userId);
        return token;
    }

    String nextOrderId() { return uuid(++orderSequence); }
    String nextTicketId() { return uuid(++ticketSequence); }

    UserRecord findUserById(String id) {
        for (UserRecord user : usersByEmail.values()) if (user.id.equals(id)) return user;
        return null;
    }

    void revokeUserTokens(String userId) {
        Iterator<Map.Entry<String, String>> iterator = tokenUsers.entrySet().iterator();
        while (iterator.hasNext()) if (userId.equals(iterator.next().getValue())) iterator.remove();
    }

    private void seedMovies() {
        addMovie(1, "A última luz", "Drama", 120, "M/12",
                "Uma viagem de regresso a Lisboa muda a forma como Leonor vê a sua cidade.");
        addMovie(2, "Entre marés", "Drama", 108, "M/12",
                "Dois irmãos voltam a encontrar-se numa pequena vila junto ao Atlântico.");
        addMovie(3, "Órbita", "Ficção científica", 126, "M/12",
                "Uma astronauta recebe uma mensagem inesperada durante a sua última missão.");
        addMovie(4, "Dias de verão", "Comédia", 96, "M/6",
                "Quatro amigos vivem um verão cheio de planos que não correm como esperado.");
        addMovie(5, "O outro lado", "Thriller", 114, "M/16",
                "Uma jornalista investiga o desaparecimento de um vizinho.");
    }

    private void addMovie(int number, String title, String genre, int duration, String rating, String synopsis) {
        String id = uuid(number);
        movies.put(id, object("id", id, "title", title, "synopsis", synopsis,
                "durationMinutes", duration, "releaseDate", "2026-10-01", "ageRating", rating,
                "posterUrl", JSONObject.NULL, "trailerUrl", JSONObject.NULL,
                "genres", array(genre)));
    }

    private void seedSessionsAndSeats() {
        String[] dates = {"2026-10-28", "2026-10-29", "2026-10-30"};
        String[] times = {"18:00", "20:30", "21:30"};
        int sessionNumber = 20;
        for (JSONObject movie : movies.values()) {
            for (String date : dates) {
                for (int timeIndex = 0; timeIndex < times.length; timeIndex++) {
                    String id = uuid(sessionNumber++);
                    String roomId = uuid(timeIndex == 2 ? 12 : 11);
                    JSONObject session = object("id", id, "movie", copy(movie),
                            "room", object("id", roomId, "name", timeIndex == 2 ? "Sala 2" : "Sala 1"),
                            "startsAt", date + "T" + times[timeIndex] + ":00Z", "price", 7.5,
                            "status", "scheduled");
                    sessions.put(id, session);
                    seedSeats(id, sessionNumber * 100);
                }
            }
        }
    }

    private void seedSeats(String sessionId, int start) {
        LinkedHashMap<String, JSONObject> seats = new LinkedHashMap<>();
        for (int row = 0; row < 5; row++) {
            for (int number = 1; number <= 8; number++) {
                String id = uuid(start + row * 8 + number);
                String status = ((row == 1 && (number == 3 || number == 4))
                        || (row == 2 && number == 6) || (row == 3 && (number == 5 || number == 6)))
                        ? "sold" : "available";
                seats.put(id, object("id", id, "row", String.valueOf((char) ('A' + row)),
                        "number", number, "status", status));
            }
        }
        seatsBySession.put(sessionId, seats);
    }

    private void seedOrdersAndTickets() {
        List<JSONObject> sessionsList = new ArrayList<>(sessions.values());
        for (int index = 0; index < 3; index++) {
            JSONObject session = sessionsList.get(index * 3 + 1);
            JSONObject seat = new ArrayList<>(seatsBySession.get(session.optString("id")).values()).get(index);
            set(seat, "status", "sold");
            String orderId = uuid(80 + index);
            String ticketId = uuid(90 + index);
            JSONObject ticket = ticket(ticketId, orderId, session, seat, "CIN-DEMO-" + (index + 1));
            JSONObject order = object("id", orderId, "total", 7.5,
                    "createdAt", "2026-10-0" + (index + 1) + "T12:00:00Z", "tickets", array(ticket));
            orders.put(orderId, order);
            tickets.put(ticketId, ticket);
            orderOwners.put(orderId, ANA_USER_ID);
            ticketOwners.put(ticketId, ANA_USER_ID);
        }
    }

    JSONObject ticket(String ticketId, String orderId, JSONObject session, JSONObject seat, String code) {
        return object("id", ticketId, "orderId", orderId, "session", sessionSummary(session),
                "seat", seatWithoutStatus(seat), "price", session.optDouble("price"), "code", code,
                "createdAt", "2026-10-28T12:00:00Z", "usedAt", JSONObject.NULL);
    }

    static JSONObject sessionSummary(JSONObject session) {
        return object("id", session.optString("id"), "startsAt", session.optString("startsAt"),
                "price", session.optDouble("price"), "status", session.optString("status"),
                "room", copy(session.optJSONObject("room")));
    }

    static JSONObject seatWithoutStatus(JSONObject seat) {
        return object("id", seat.optString("id"), "row", seat.optString("row"),
                "number", seat.optInt("number"));
    }

    static JSONObject copy(JSONObject value) {
        if (value == null) return new JSONObject();
        try {
            return new JSONObject(value.toString());
        } catch (JSONException exception) {
            throw new IllegalStateException(exception);
        }
    }

    static JSONArray array(Object... values) {
        JSONArray result = new JSONArray();
        for (Object value : values) result.put(value);
        return result;
    }

    static JSONObject object(Object... values) {
        JSONObject result = new JSONObject();
        try {
            for (int index = 0; index < values.length; index += 2) {
                result.put(String.valueOf(values[index]), values[index + 1]);
            }
            return result;
        } catch (JSONException exception) {
            throw new IllegalStateException(exception);
        }
    }

    static void set(JSONObject object, String key, Object value) {
        try {
            object.put(key, value);
        } catch (JSONException exception) {
            throw new IllegalStateException(exception);
        }
    }

    static String uuid(int number) {
        return String.format("%08d-0000-4000-8000-%012d", number, number);
    }

    static final class UserRecord {
        final String id;
        String name;
        String email;
        final String password;
        final String createdAt;

        UserRecord(String id, String name, String email, String password, String createdAt) {
            this.id = id;
            this.name = name;
            this.email = email;
            this.password = password;
            this.createdAt = createdAt;
        }

        JSONObject json() {
            return object("id", id, "name", name, "email", email, "role", "customer",
                    "createdAt", createdAt);
        }
    }
}
