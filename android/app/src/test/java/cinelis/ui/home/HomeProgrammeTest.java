package cinelis.ui.home;

import static org.junit.Assert.assertEquals;

import org.junit.Test;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.Collections;
import java.util.List;

import cinelis.model.CinemaSession;
import cinelis.model.Movie;

public class HomeProgrammeTest {
    @Test public void availableDatesFollowThePrototypeClockInDisplayOrder() {
        List<ProgrammeDate> dates = ProgrammeDate.availableDates();

        assertEquals(Arrays.asList("2026-10-28", "2026-10-29", "2026-10-30"),
                Arrays.asList(dates.get(0).getIsoDate(), dates.get(1).getIsoDate(), dates.get(2).getIsoDate()));
        assertEquals(Arrays.asList("Hoje", "Amanhã", "30 out."),
                Arrays.asList(dates.get(0).getLabel(), dates.get(1).getLabel(), dates.get(2).getLabel()));
    }

    @Test public void programmeCopiesAndSortsSessionsChronologically() {
        Movie movie = movie();
        List<CinemaSession> source = new ArrayList<>(Arrays.asList(
                session("late", movie, "2026-10-28T21:30:00Z"),
                session("early", movie, "2026-10-28T18:00:00Z"),
                session("middle", movie, "2026-10-28T20:30:00Z")));

        HomeProgramme programme = new HomeProgramme(movie, source);
        source.clear();

        assertEquals(Arrays.asList("early", "middle", "late"), Arrays.asList(
                programme.getSessions().get(0).getId(), programme.getSessions().get(1).getId(),
                programme.getSessions().get(2).getId()));
    }

    @Test(expected = UnsupportedOperationException.class)
    public void programmeDoesNotExposeMutableSessionState() {
        HomeProgramme programme = new HomeProgramme(movie(), Collections.singletonList(
                session("one", movie(), "2026-10-28T18:00:00Z")));

        programme.getSessions().clear();
    }

    private static Movie movie() {
        return new Movie("movie", "A última luz", "Sinopse", 120, "2026-10-01",
                "M/12", "", "", Collections.singletonList("Drama"));
    }

    private static CinemaSession session(String id, Movie movie, String startsAt) {
        return new CinemaSession(id, movie, "room", "Sala 1", startsAt, 7.5, "scheduled");
    }
}
