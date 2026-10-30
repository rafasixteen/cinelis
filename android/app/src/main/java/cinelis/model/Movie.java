package cinelis.model;

import java.util.ArrayList;
import java.util.Collections;
import java.util.List;

public final class Movie {
    private final String id;
    private final String title;
    private final String synopsis;
    private final int durationMinutes;
    private final String releaseDate;
    private final String ageRating;
    private final String posterUrl;
    private final String trailerUrl;
    private final List<String> genres;

    public Movie(String id, String title, String synopsis, int durationMinutes, String releaseDate,
                 String ageRating, String posterUrl, String trailerUrl, List<String> genres) {
        this.id = id;
        this.title = title;
        this.synopsis = synopsis;
        this.durationMinutes = durationMinutes;
        this.releaseDate = releaseDate;
        this.ageRating = ageRating;
        this.posterUrl = posterUrl;
        this.trailerUrl = trailerUrl;
        this.genres = Collections.unmodifiableList(new ArrayList<>(genres));
    }

    public String getId() { return id; }
    public String getTitle() { return title; }
    public String getSynopsis() { return synopsis; }
    public int getDurationMinutes() { return durationMinutes; }
    public String getReleaseDate() { return releaseDate; }
    public String getAgeRating() { return ageRating; }
    public String getPosterUrl() { return posterUrl; }
    public String getTrailerUrl() { return trailerUrl; }
    public List<String> getGenres() { return genres; }
}
