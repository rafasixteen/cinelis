package cinelis.ui.home;

import java.util.ArrayList;
import java.util.Collections;
import java.util.Comparator;
import java.util.List;

import cinelis.model.CinemaSession;
import cinelis.model.Movie;

public final class HomeProgramme {
    private final Movie movie;
    private final List<CinemaSession> sessions;

    public HomeProgramme(Movie movie, List<CinemaSession> sessions) {
        this.movie = movie;
        ArrayList<CinemaSession> ordered = new ArrayList<>(sessions);
        ordered.sort(Comparator.comparing(CinemaSession::getStartsAt));
        this.sessions = Collections.unmodifiableList(ordered);
    }

    public Movie getMovie() { return movie; }
    public List<CinemaSession> getSessions() { return sessions; }
}
