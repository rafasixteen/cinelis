package cinelis.ui.home;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import java.util.ArrayList;
import java.util.List;

import cinelis.R;
import cinelis.model.Movie;
import cinelis.ui.common.MoviePosterView;

public final class MovieAdapter extends RecyclerView.Adapter<MovieAdapter.Holder> {
    public interface Listener { void onMovieSelected(Movie movie); }
    private final List<Movie> movies = new ArrayList<>();
    private final Listener listener;
    private final boolean compact;

    public MovieAdapter(Listener listener) { this(listener, false); }
    public MovieAdapter(Listener listener, boolean compact) {
        this.listener = listener;
        this.compact = compact;
    }

    public void submit(List<Movie> values) {
        movies.clear();
        movies.addAll(values);
        notifyDataSetChanged();
    }

    @NonNull @Override public Holder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(
                compact ? R.layout.item_upcoming_movie : R.layout.item_movie, parent, false);
        if (!compact) {
            float density = parent.getResources().getDisplayMetrics().density;
            int width = parent.getWidth() > 0 ? parent.getWidth() : parent.getResources().getDisplayMetrics().widthPixels;
            view.getLayoutParams().width = Math.max((int)(140*density), (width-(int)(50*density))/2);
        }
        return new Holder(view);
    }

    @Override public void onBindViewHolder(@NonNull Holder holder, int position) {
        Movie movie = movies.get(position);
        holder.title.setText(movie.getTitle());
        String genre = movie.getGenres().isEmpty() ? "" : movie.getGenres().get(0);
        holder.meta.setText(compact ? genre + " · " + movie.getDurationMinutes() + " min"
                : movie.getAgeRating() + " · " + movie.getDurationMinutes() + " min");
        if (holder.poster != null) holder.poster.setMovie(movie);
        if (holder.rating != null) holder.rating.setText(movie.getAgeRating());
        View sessions = holder.itemView.findViewById(R.id.movie_sessions);
        if (sessions != null) sessions.setOnClickListener(view -> listener.onMovieSelected(movie));
        holder.itemView.setContentDescription("Ver sessões de " + movie.getTitle());
        holder.itemView.setOnClickListener(view -> listener.onMovieSelected(movie));
    }

    @Override public int getItemCount() { return movies.size(); }

    public static int poster(String title) {
        if (title.contains("última")) return R.drawable.poster_last_light;
        if (title.contains("marés")) return R.drawable.poster_between_tides;
        if (title.contains("Órbita")) return R.drawable.poster_orbit;
        if (title.contains("verão")) return R.drawable.poster_summer_days;
        return R.drawable.poster_other_side;
    }

    static final class Holder extends RecyclerView.ViewHolder {
        final MoviePosterView poster;
        final TextView title;
        final TextView meta;
        final TextView rating;
        Holder(View itemView) {
            super(itemView);
            poster = itemView.findViewById(R.id.movie_poster);
            title = itemView.findViewById(R.id.movie_title);
            meta = itemView.findViewById(R.id.movie_meta);
            rating = itemView.findViewById(R.id.movie_rating);
        }
    }
}
