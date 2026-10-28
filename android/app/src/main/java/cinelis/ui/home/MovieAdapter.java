package cinelis.ui.home;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import java.util.ArrayList;
import java.util.List;

import cinelis.R;
import cinelis.model.Movie;

public final class MovieAdapter extends RecyclerView.Adapter<MovieAdapter.Holder> {
    public interface Listener { void onMovieSelected(Movie movie); }
    private final List<Movie> movies = new ArrayList<>();
    private final Listener listener;

    public MovieAdapter(Listener listener) { this.listener = listener; }

    public void submit(List<Movie> values) {
        movies.clear();
        movies.addAll(values);
        notifyDataSetChanged();
    }

    @NonNull @Override public Holder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        return new Holder(LayoutInflater.from(parent.getContext()).inflate(R.layout.item_movie, parent, false));
    }

    @Override public void onBindViewHolder(@NonNull Holder holder, int position) {
        Movie movie = movies.get(position);
        holder.title.setText(movie.getTitle());
        String genre = movie.getGenres().isEmpty() ? "" : movie.getGenres().get(0);
        holder.meta.setText(movie.getDurationMinutes() + " min · " + genre);
        holder.poster.setImageResource(poster(movie.getTitle()));
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
        final ImageView poster;
        final TextView title;
        final TextView meta;
        Holder(View itemView) {
            super(itemView);
            poster = itemView.findViewById(R.id.movie_poster);
            title = itemView.findViewById(R.id.movie_title);
            meta = itemView.findViewById(R.id.movie_meta);
        }
    }
}
