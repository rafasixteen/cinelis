package cinelis.ui.home;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.LinearLayout;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import java.util.ArrayList;
import java.util.List;

import cinelis.R;
import cinelis.model.CinemaSession;
import cinelis.model.Movie;
import cinelis.ui.common.MoviePosterView;

public final class HomeProgrammeAdapter extends RecyclerView.Adapter<HomeProgrammeAdapter.Holder> {
    public interface Listener {
        void onMovieSelected(Movie movie);
        void onSessionSelected(CinemaSession session);
    }

    private final List<HomeProgramme> programmes = new ArrayList<>();
    private final Listener listener;

    public HomeProgrammeAdapter(Listener listener) { this.listener = listener; }

    public void submit(List<HomeProgramme> values) {
        programmes.clear();
        programmes.addAll(values);
        notifyDataSetChanged();
    }

    @NonNull @Override public Holder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        return new Holder(LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_home_programme, parent, false));
    }

    @Override public void onBindViewHolder(@NonNull Holder holder, int position) {
        HomeProgramme programme = programmes.get(position);
        Movie movie = programme.getMovie();
        holder.poster.setMovie(movie);
        holder.title.setText(movie.getTitle());
        String genre = movie.getGenres().isEmpty() ? "" : movie.getGenres().get(0);
        holder.meta.setText(genre + " · " + movie.getDurationMinutes() + " min");
        holder.rating.setText(movie.getAgeRating());
        holder.summary.setContentDescription("Ver detalhes de " + movie.getTitle());
        holder.summary.setOnClickListener(view -> listener.onMovieSelected(movie));

        holder.sessionTimes.removeAllViews();
        LayoutInflater inflater = LayoutInflater.from(holder.itemView.getContext());
        for (CinemaSession session : programme.getSessions()) {
            Button button = (Button) inflater.inflate(
                    R.layout.item_session_time, holder.sessionTimes, false);
            String time = time(session.getStartsAt());
            button.setText(time);
            button.setContentDescription("Escolher sessão " + time + " de " + movie.getTitle());
            button.setOnClickListener(view -> listener.onSessionSelected(session));
            holder.sessionTimes.addView(button);
        }
    }

    @Override public int getItemCount() { return programmes.size(); }

    private static String time(String startsAt) {
        return startsAt != null && startsAt.length() >= 16 ? startsAt.substring(11, 16) : startsAt;
    }

    static final class Holder extends RecyclerView.ViewHolder {
        final View summary;
        final MoviePosterView poster;
        final TextView title;
        final TextView meta;
        final TextView rating;
        final LinearLayout sessionTimes;

        Holder(View view) {
            super(view);
            summary = view.findViewById(R.id.movie_summary);
            poster = view.findViewById(R.id.movie_poster);
            title = view.findViewById(R.id.movie_title);
            meta = view.findViewById(R.id.movie_meta);
            rating = view.findViewById(R.id.movie_rating);
            sessionTimes = view.findViewById(R.id.session_times);
        }
    }
}
