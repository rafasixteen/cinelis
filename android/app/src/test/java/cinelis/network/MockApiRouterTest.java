package cinelis.network;

import static org.junit.Assert.assertEquals;
import static org.junit.Assert.assertFalse;
import static org.junit.Assert.assertNotNull;
import static org.junit.Assert.assertTrue;

import com.android.volley.Request;

import org.json.JSONArray;
import org.json.JSONObject;
import org.junit.Before;
import org.junit.Test;

import java.nio.charset.StandardCharsets;
import java.util.Collections;
import java.util.HashMap;
import java.util.Map;

public class MockApiRouterTest {
    private MockApiRouter router;

    @Before
    public void setUp() {
        router = new MockApiRouter(new MockApiStore());
    }

    @Test
    public void loginLogoutAndProtectedProfileFollowOpenApiStatuses() throws Exception {
        MockApiResponse login = request(Request.Method.POST, "/auth/login",
                json("{\"email\":\"ana@exemplo.pt\",\"password\":\"password123\"}"), null);
        assertEquals(200, login.getStatusCode());
        JSONObject payload = object(login);
        assertTrue(payload.has("expiresAt"));
        assertEquals("ana@exemplo.pt", payload.getJSONObject("user").getString("email"));

        String token = payload.getString("token");
        assertEquals(200, request(Request.Method.GET, "/auth/me", null, token).getStatusCode());
        assertEquals(204, request(Request.Method.POST, "/auth/logout", null, token).getStatusCode());
        assertEquals(401, request(Request.Method.GET, "/auth/me", null, token).getStatusCode());
    }

    @Test
    public void registerUpdateAndDeleteAccountAreVolatile() throws Exception {
        byte[] registration = json("{\"name\":\"João Silva\",\"email\":\"joao@example.com\",\"password\":\"password123\"}");
        assertEquals(201, request(Request.Method.POST, "/auth/register", registration, null).getStatusCode());
        assertEquals(409, request(Request.Method.POST, "/auth/register", registration, null).getStatusCode());

        String token = login("joao@example.com", "password123");
        MockApiResponse updated = request(Request.Method.PUT, "/auth/me",
                json("{\"name\":\"João Ferreira\",\"email\":\"joao.novo@example.com\"}"), token);
        assertEquals(200, updated.getStatusCode());
        assertEquals("João Ferreira", object(updated).getString("name"));
        assertEquals(204, request(Request.Method.DELETE, "/auth/me", null, token).getStatusCode());
        assertEquals(401, request(Request.Method.GET, "/auth/me", null, token).getStatusCode());
    }

    @Test
    public void validationAndUnauthorizedErrorsUseDocumentedShapes() throws Exception {
        MockApiResponse invalid = request(Request.Method.POST, "/auth/register", json("{\"name\":\"\"}"), null);
        assertEquals(422, invalid.getStatusCode());
        assertEquals("validation_error", object(invalid).getString("code"));
        assertTrue(object(invalid).getJSONObject("fields").has("email"));

        MockApiResponse unauthorized = request(Request.Method.GET, "/orders", null, null);
        assertEquals(401, unauthorized.getStatusCode());
        assertTrue(object(unauthorized).has("message"));
    }

    @Test
    public void movieRoutesSupportSearchGenreDetailsSessionsAndUrlNormalization() throws Exception {
        MockApiResponse all = request(Request.Method.GET, "https://example.invalid/v1//movies/?perPage=20", null, null);
        assertEquals(200, all.getStatusCode());
        assertEquals(5, object(all).getJSONArray("items").length());

        MockApiResponse filtered = request(Request.Method.GET,
                "http://localhost:8080/v1/movies?q=%C3%B3rbita&genre=Fic%C3%A7%C3%A3o%20cient%C3%ADfica", null, null);
        JSONArray items = object(filtered).getJSONArray("items");
        assertEquals(1, items.length());
        String movieId = items.getJSONObject(0).getString("id");
        assertEquals(200, request(Request.Method.GET, "/movies/" + movieId + "/", null, null).getStatusCode());

        JSONArray sessions = array(request(Request.Method.GET,
                "/movies/" + movieId + "/sessions?date=2026-10-28", null, null));
        assertEquals(3, sessions.length());
        assertTrue(sessions.getJSONObject(0).has("room"));
        assertEquals(404, request(Request.Method.GET, "/movies/00000000-0000-0000-0000-000000000000", null, null).getStatusCode());
    }

    @Test
    public void sessionSeatsHoldsAndOrdersSupportHappyPathAndConflicts() throws Exception {
        JSONObject movies = object(request(Request.Method.GET, "/movies", null, null));
        String movieId = movies.getJSONArray("items").getJSONObject(0).getString("id");
        JSONObject session = array(request(Request.Method.GET,
                "/movies/" + movieId + "/sessions", null, null)).getJSONObject(0);
        String sessionId = session.getString("id");
        assertEquals(200, request(Request.Method.GET, "/sessions/" + sessionId, null, null).getStatusCode());

        JSONObject seatPayload = object(request(Request.Method.GET,
                "/sessions/" + sessionId + "/seats", null, null));
        assertEquals(40, seatPayload.getJSONArray("seats").length());
        String seatId = firstSeatWithStatus(seatPayload.getJSONArray("seats"), "available");
        String token = login("ana@exemplo.pt", "password123");
        byte[] holdBody = json("{\"seatIds\":[\"" + seatId + "\"]}");
        assertEquals(201, request(Request.Method.POST,
                "/sessions/" + sessionId + "/holds", holdBody, token).getStatusCode());
        assertEquals(409, request(Request.Method.POST,
                "/sessions/" + sessionId + "/holds", holdBody, token).getStatusCode());

        MockApiResponse order = request(Request.Method.POST, "/orders",
                json("{\"sessionId\":\"" + sessionId + "\",\"seatIds\":[\"" + seatId + "\"]}"), token);
        assertEquals(201, order.getStatusCode());
        String orderId = object(order).getString("id");
        assertEquals(200, request(Request.Method.GET, "/orders", null, token).getStatusCode());
        assertEquals(200, request(Request.Method.GET, "/orders/" + orderId, null, token).getStatusCode());
    }

    @Test
    public void ticketListDetailAndUpcomingFilterAreAvailableToAuthenticatedUser() throws Exception {
        String token = login("ana@exemplo.pt", "password123");
        JSONObject tickets = object(request(Request.Method.GET, "/tickets?upcoming=true", null, token));
        assertTrue(tickets.getJSONArray("items").length() >= 3);
        String ticketId = tickets.getJSONArray("items").getJSONObject(0).getString("id");
        MockApiResponse detail = request(Request.Method.GET, "/tickets/" + ticketId, null, token);
        assertEquals(200, detail.getStatusCode());
        assertTrue(object(detail).has("code"));
        assertEquals(404, request(Request.Method.GET,
                "/tickets/00000000-0000-0000-0000-000000000000", null, token).getStatusCode());
    }

    @Test
    public void freshStoreRestoresSeedDataInsteadOfPriorMutations() throws Exception {
        String token = login("ana@exemplo.pt", "password123");
        assertEquals(204, request(Request.Method.DELETE, "/auth/me", null, token).getStatusCode());
        MockApiRouter fresh = new MockApiRouter(new MockApiStore());
        MockApiResponse login = fresh.route(Request.Method.POST, "/auth/login", Collections.emptyMap(),
                json("{\"email\":\"ana@exemplo.pt\",\"password\":\"password123\"}"));
        assertEquals(200, login.getStatusCode());
    }

    private String login(String email, String password) throws Exception {
        MockApiResponse response = request(Request.Method.POST, "/auth/login",
                json("{\"email\":\"" + email + "\",\"password\":\"" + password + "\"}"), null);
        return object(response).getString("token");
    }

    private MockApiResponse request(int method, String path, byte[] body, String token) {
        Map<String, String> headers = new HashMap<>();
        if (token != null) {
            headers.put("Authorization", "Bearer " + token);
        }
        return router.route(method, path, headers, body);
    }

    private static JSONObject object(MockApiResponse response) throws Exception {
        return new JSONObject(new String(response.getBody(), StandardCharsets.UTF_8));
    }

    private static JSONArray array(MockApiResponse response) throws Exception {
        return new JSONArray(new String(response.getBody(), StandardCharsets.UTF_8));
    }

    private static byte[] json(String value) {
        return value.getBytes(StandardCharsets.UTF_8);
    }

    private static String firstSeatWithStatus(JSONArray seats, String status) throws Exception {
        for (int index = 0; index < seats.length(); index++) {
            JSONObject seat = seats.getJSONObject(index);
            if (status.equals(seat.getString("status"))) {
                return seat.getString("id");
            }
        }
        assertFalse("Expected a seat with status " + status, true);
        return null;
    }
}
