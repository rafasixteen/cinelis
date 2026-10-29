package cinelis.ui.home;

import android.os.Bundle;
import android.text.Editable;
import android.text.TextWatcher;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.navigation.fragment.NavHostFragment;
import androidx.recyclerview.widget.LinearLayoutManager;

import java.util.ArrayList;
import java.util.List;
import java.util.Locale;

import cinelis.databinding.FragmentHomeBinding;
import cinelis.model.Movie;
import cinelis.navigation.AppNavigator;
import cinelis.network.ApiCallback;
import cinelis.network.ApiClient;
import cinelis.network.ApiError;
import cinelis.ui.common.UiStateController;

public class HomeFragment extends Fragment {
    private FragmentHomeBinding binding;
    private MovieAdapter currentAdapter;
    private MovieAdapter upcomingAdapter;
    private UiStateController state;
    private final List<Movie> catalogue = new ArrayList<>();

    @Nullable @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container,
                             @Nullable Bundle savedInstanceState) {
        binding = FragmentHomeBinding.inflate(inflater, container, false);
        state = new UiStateController(binding.loadingContainer, binding.contentContainer,
                binding.emptyContainer, binding.errorContainer, binding.errorMessage, binding.retryButton);
        MovieAdapter.Listener listener = movie -> new AppNavigator(
                NavHostFragment.findNavController(this)).showMovie(movie.getId());
        currentAdapter = new MovieAdapter(listener);
        upcomingAdapter = new MovieAdapter(listener);
        binding.currentMovies.setLayoutManager(new LinearLayoutManager(requireContext(), LinearLayoutManager.HORIZONTAL, false));
        binding.currentMovies.setAdapter(currentAdapter);
        binding.upcomingMovies.setLayoutManager(new LinearLayoutManager(requireContext(), LinearLayoutManager.HORIZONTAL, false));
        binding.upcomingMovies.setAdapter(upcomingAdapter);
        binding.searchInput.addTextChangedListener(new TextWatcher() {
            @Override public void beforeTextChanged(CharSequence value, int start, int count, int after) {}
            @Override public void onTextChanged(CharSequence value, int start, int before, int count) { filter(value.toString()); }
            @Override public void afterTextChanged(Editable value) {}
        });
        binding.clearSearchButton.setOnClickListener(view -> binding.searchInput.setText(""));
        loadMovies();
        return binding.getRoot();
    }

    public void focusSearch() {
        if (binding != null) binding.searchInput.requestFocus();
    }

    private void loadMovies() {
        state.showLoading();
        ApiClient.get().listMovies("", "", this, new ApiCallback<List<Movie>>() {
            @Override public void onSuccess(List<Movie> movies) {
                if (binding == null) return;
                catalogue.clear();
                catalogue.addAll(movies);
                filter(binding.searchInput.getText().toString());
            }
            @Override public void onError(ApiError error) {
                if (binding != null) state.showError(error.getMessage(), HomeFragment.this::loadMovies);
            }
        });
    }

    private void filter(String query) {
        if (binding == null) return;
        if (catalogue.isEmpty()) {
            currentAdapter.submit(new ArrayList<>());
            upcomingAdapter.submit(new ArrayList<>());
            state.showEmpty();
            return;
        }
        String normalized = query.trim().toLowerCase(Locale.ROOT);
        List<Movie> result = new ArrayList<>();
        for (Movie movie : catalogue) {
            String genre = movie.getGenres().isEmpty() ? "" : movie.getGenres().get(0);
            if (normalized.isEmpty() || (movie.getTitle() + " " + genre).toLowerCase(Locale.ROOT).contains(normalized)) {
                result.add(movie);
            }
        }
        currentAdapter.submit(result);
        upcomingAdapter.submit(result.size() > 3 ? result.subList(2, result.size()) : result);
        if (result.isEmpty()) state.showEmpty(); else state.showContent();
    }

    @Override public void onDestroyView() {
        ApiClient.get().cancel(this);
        binding = null;
        super.onDestroyView();
    }
}
