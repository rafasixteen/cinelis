package cinelis.network;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;

import cinelis.model.CinemaSession;
import cinelis.model.Movie;
import cinelis.model.Order;
import cinelis.model.Seat;
import cinelis.model.Ticket;
import cinelis.model.User;

public final class JsonModelMapper {
    private JsonModelMapper() {}

    public static User user(JSONObject json) {
        return new User(json.optString("id"), json.optString("name"), json.optString("email"),
                json.optString("role"), json.optString("createdAt"));
    }

    public static Movie movie(JSONObject json) {
        List<String> genres = new ArrayList<>();
        JSONArray values = json.optJSONArray("genres");
        if (values != null) {
            for (int index = 0; index < values.length(); index++) {
                genres.add(values.optString(index));
            }
        }
        return new Movie(json.optString("id"), json.optString("title"), nullable(json, "synopsis"),
                json.optInt("durationMinutes"), nullable(json, "releaseDate"),
                nullable(json, "ageRating"), nullable(json, "posterUrl"),
                nullable(json, "trailerUrl"), genres);
    }

    public static CinemaSession session(JSONObject json) {
        JSONObject room = json.optJSONObject("room");
        JSONObject movie = json.optJSONObject("movie");
        return new CinemaSession(json.optString("id"), movie == null ? null : movie(movie),
                room == null ? "" : room.optString("id"), room == null ? "" : room.optString("name"),
                json.optString("startsAt"), json.optDouble("price"), json.optString("status"));
    }

    public static Seat seat(JSONObject json) {
        return new Seat(json.optString("id"), json.optString("row"), json.optInt("number"),
                nullable(json, "status"));
    }

    public static Order order(JSONObject json) {
        return new Order(json.optString("id"), json.optDouble("total"), json.optString("createdAt"),
                tickets(json.optJSONArray("tickets")));
    }

    public static Ticket ticket(JSONObject json) {
        return new Ticket(json.optString("id"), json.optString("orderId"),
                session(json.optJSONObject("session")), seat(json.optJSONObject("seat")),
                json.optDouble("price"), json.optString("code"), json.optString("createdAt"),
                nullable(json, "usedAt"));
    }

    public static List<User> users(JSONArray json) {
        List<User> result = new ArrayList<>();
        if (json != null) for (int i = 0; i < json.length(); i++) result.add(user(json.optJSONObject(i)));
        return result;
    }

    public static List<Movie> movies(JSONArray json) {
        List<Movie> result = new ArrayList<>();
        if (json != null) for (int i = 0; i < json.length(); i++) result.add(movie(json.optJSONObject(i)));
        return result;
    }

    public static List<CinemaSession> sessions(JSONArray json) {
        List<CinemaSession> result = new ArrayList<>();
        if (json != null) for (int i = 0; i < json.length(); i++) result.add(session(json.optJSONObject(i)));
        return result;
    }

    public static List<Seat> seats(JSONArray json) {
        List<Seat> result = new ArrayList<>();
        if (json != null) for (int i = 0; i < json.length(); i++) result.add(seat(json.optJSONObject(i)));
        return result;
    }

    public static List<Order> orders(JSONArray json) {
        List<Order> result = new ArrayList<>();
        if (json != null) for (int i = 0; i < json.length(); i++) result.add(order(json.optJSONObject(i)));
        return result;
    }

    public static List<Ticket> tickets(JSONArray json) {
        List<Ticket> result = new ArrayList<>();
        if (json != null) for (int i = 0; i < json.length(); i++) result.add(ticket(json.optJSONObject(i)));
        return result;
    }

    private static String nullable(JSONObject json, String key) {
        return json == null || json.isNull(key) || !json.has(key) ? null : json.optString(key, null);
    }
}
