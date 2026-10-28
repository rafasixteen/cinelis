package cinelis.network;

import static org.junit.Assert.assertEquals;
import static org.junit.Assert.assertNull;

import org.json.JSONArray;
import org.json.JSONObject;
import org.junit.Test;

import cinelis.model.CinemaSession;
import cinelis.model.Movie;
import cinelis.model.Order;
import cinelis.model.Seat;
import cinelis.model.Ticket;
import cinelis.model.User;

public class JsonModelMapperTest {
    @Test
    public void mapsAllOpenApiDisplayModels() throws Exception {
        User user = JsonModelMapper.user(new JSONObject("{\"id\":\"u1\",\"name\":\"Ana\",\"email\":\"ana@example.com\",\"role\":\"customer\",\"createdAt\":\"2026-10-01T10:00:00Z\"}"));
        Movie movie = JsonModelMapper.movie(new JSONObject("{\"id\":\"m1\",\"title\":\"Órbita\",\"synopsis\":null,\"durationMinutes\":126,\"releaseDate\":\"2026-10-01\",\"ageRating\":\"M/12\",\"posterUrl\":null,\"trailerUrl\":null,\"genres\":[\"Ficção científica\"]}"));
        CinemaSession session = JsonModelMapper.session(new JSONObject("{\"id\":\"s1\",\"movie\":" + movieJson() + ",\"room\":{\"id\":\"r1\",\"name\":\"Sala 1\"},\"startsAt\":\"2026-10-28T20:30:00Z\",\"price\":7.5,\"status\":\"scheduled\"}"));
        Seat seat = JsonModelMapper.seat(new JSONObject("{\"id\":\"seat1\",\"row\":\"A\",\"number\":2,\"status\":\"available\"}"));
        Ticket ticket = JsonModelMapper.ticket(new JSONObject(ticketJson()));
        Order order = JsonModelMapper.order(new JSONObject("{\"id\":\"o1\",\"total\":7.5,\"createdAt\":\"2026-10-01T10:00:00Z\",\"tickets\":[" + ticketJson() + "]}"));

        assertEquals("Ana", user.getName());
        assertNull(movie.getSynopsis());
        assertEquals("Sala 1", session.getRoomName());
        assertEquals("available", seat.getStatus());
        assertEquals("CIN-001", ticket.getCode());
        assertEquals(1, order.getTickets().size());
    }

    @Test
    public void listHelpersPreserveArrayOrder() throws Exception {
        JSONArray movies = new JSONArray("[" + movieJson() + "," + movieJson().replace("Órbita", "Entre marés").replace("m1", "m2") + "]");
        assertEquals("Entre marés", JsonModelMapper.movies(movies).get(1).getTitle());
    }

    private static String movieJson() {
        return "{\"id\":\"m1\",\"title\":\"Órbita\",\"synopsis\":\"Sinopse\",\"durationMinutes\":126,\"releaseDate\":\"2026-10-01\",\"ageRating\":\"M/12\",\"posterUrl\":null,\"trailerUrl\":null,\"genres\":[\"Ficção científica\"]}";
    }

    private static String ticketJson() {
        return "{\"id\":\"t1\",\"orderId\":\"o1\",\"session\":{\"id\":\"s1\",\"startsAt\":\"2026-10-28T20:30:00Z\",\"price\":7.5,\"status\":\"scheduled\",\"room\":{\"id\":\"r1\",\"name\":\"Sala 1\"}},\"seat\":{\"id\":\"seat1\",\"row\":\"A\",\"number\":2},\"price\":7.5,\"code\":\"CIN-001\",\"createdAt\":\"2026-10-01T10:00:00Z\",\"usedAt\":null}";
    }
}
