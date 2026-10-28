package cinelis.ui.movie;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.navigation.fragment.NavHostFragment;
import androidx.recyclerview.widget.LinearLayoutManager;

import com.google.android.material.snackbar.Snackbar;

import java.util.List;

import cinelis.R;
import cinelis.databinding.FragmentMovieDetailBinding;
import cinelis.model.CinemaSession;
import cinelis.model.Movie;
import cinelis.navigation.AppNavigator;
import cinelis.network.ApiCallback;
import cinelis.network.ApiClient;
import cinelis.network.ApiError;
import cinelis.ui.common.UiStateController;
import cinelis.ui.home.MovieAdapter;

public class MovieDetailFragment extends Fragment {
    private FragmentMovieDetailBinding binding;
    private UiStateController state;
    private SessionAdapter adapter;
    private String movieId;
    private String selectedDate = "2026-10-28";

    @Nullable @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container,
                             @Nullable Bundle savedInstanceState) {
        binding = FragmentMovieDetailBinding.inflate(inflater, container, false);
        if (savedInstanceState != null) selectedDate = savedInstanceState.getString("selectedDate", selectedDate);
        state = new UiStateController(binding.loadingContainer, binding.contentContainer,
                binding.emptyContainer, binding.errorContainer, binding.errorMessage, binding.retryButton);
        adapter = new SessionAdapter(this::selectSession);
        binding.sessionsList.setLayoutManager(new LinearLayoutManager(requireContext()));
        binding.sessionsList.setAdapter(adapter);
        binding.trailerButton.setOnClickListener(view -> Snackbar.make(view, R.string.trailer_demo, Snackbar.LENGTH_SHORT).show());
        binding.dateToday.setOnClickListener(view -> loadSessions("2026-10-28"));
        binding.dateTomorrow.setOnClickListener(view -> loadSessions("2026-10-29"));
        binding.dateLater.setOnClickListener(view -> loadSessions("2026-10-30"));
        movieId = getArguments() == null ? null : getArguments().getString(AppNavigator.ARG_MOVIE_ID);
        loadMovie();
        return binding.getRoot();
    }

    private void loadMovie() {
        state.showLoading();
        ApiClient.get().getMovie(movieId, this, new ApiCallback<Movie>() {
            @Override public void onSuccess(Movie movie) {
                if (binding == null) return;
                binding.movieTitle.setText(movie.getTitle());
                String genre = movie.getGenres().isEmpty() ? "" : movie.getGenres().get(0);
                binding.movieMeta.setText(movie.getDurationMinutes() + " min · " + genre + " · " + movie.getAgeRating());
                binding.movieSynopsis.setText(movie.getSynopsis());
                binding.detailPoster.setImageResource(MovieAdapter.poster(movie.getTitle()));
                loadSessions(selectedDate);
            }
            @Override public void onError(ApiError error) {
                if (binding != null) state.showError(error.getMessage(), MovieDetailFragment.this::loadMovie);
            }
        });
    }

    private void loadSessions(String date) {
        selectedDate = date;
        ApiClient.get().listMovieSessions(movieId, date, this, new ApiCallback<List<CinemaSession>>() {
            @Override public void onSuccess(List<CinemaSession> sessions) {
                if (binding == null) return;
                adapter.submit(sessions);
                if (sessions.isEmpty()) state.showEmpty(); else state.showContent();
            }
            @Override public void onError(ApiError error) {
                if (binding != null) state.showError(error.getMessage(), MovieDetailFragment.this::loadMovie);
            }
        });
    }

    private void selectSession(CinemaSession session) {
        AppNavigator navigator = new AppNavigator(NavHostFragment.findNavController(this));
        if (ApiClient.get().isAuthenticated()) navigator.showSeats(session.getId());
        else navigator.showAuth(R.id.seatSelectionFragment, session.getId());
    }

    @Override public void onSaveInstanceState(@NonNull Bundle outState) {
        super.onSaveInstanceState(outState);
        outState.putString("selectedDate", selectedDate);
    }

    @Override public void onDestroyView() {
        ApiClient.get().cancel(this);
        binding = null;
        super.onDestroyView();
    }
}
