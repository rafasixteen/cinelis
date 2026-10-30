package cinelis.ui.home;

import android.os.Bundle;
import android.text.Editable;
import android.text.TextWatcher;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.navigation.fragment.NavHostFragment;
import androidx.recyclerview.widget.LinearLayoutManager;

import java.util.ArrayList;
import java.util.List;
import java.util.Locale;

import cinelis.R;
import cinelis.databinding.FragmentHomeBinding;
import cinelis.model.CinemaSession;
import cinelis.model.Movie;
import cinelis.navigation.AppNavigator;
import cinelis.network.ApiCallback;
import cinelis.network.ApiClient;
import cinelis.network.ApiError;
import cinelis.ui.common.UiStateController;

public class HomeFragment extends Fragment {
    private static final String STATE_SELECTED_DATE = "selectedProgrammeDate";
    private FragmentHomeBinding binding;
    private HomeProgrammeAdapter programmeAdapter;
    private MovieAdapter upcomingAdapter;
    private UiStateController state;
    private boolean catalogueLoaded;
    private int selectedDateIndex;
    private int programmeGeneration;
    private final List<Movie> catalogue = new ArrayList<>();
    private final List<Button> dateButtons = new ArrayList<>();

    @Nullable @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container,
                             @Nullable Bundle savedInstanceState) {
        binding = FragmentHomeBinding.inflate(inflater, container, false);
        state = new UiStateController(binding.loadingContainer, binding.contentContainer,
                binding.emptyContainer, binding.errorContainer, binding.errorMessage, binding.retryButton);
        if (savedInstanceState != null) {
            selectedDateIndex = savedInstanceState.getInt(STATE_SELECTED_DATE, 0);
        }

        programmeAdapter = new HomeProgrammeAdapter(new HomeProgrammeAdapter.Listener() {
            @Override public void onMovieSelected(Movie movie) {
                navigator().showMovie(movie.getId());
            }
            @Override public void onSessionSelected(CinemaSession session) {
                navigator().showSeats(session.getId());
            }
        });
        upcomingAdapter = new MovieAdapter(movie -> navigator().showMovie(movie.getId()), true);
        binding.currentMovies.setLayoutManager(new LinearLayoutManager(requireContext()));
        binding.currentMovies.setAdapter(programmeAdapter);
        binding.upcomingMovies.setLayoutManager(new LinearLayoutManager(requireContext()));
        binding.upcomingMovies.setAdapter(upcomingAdapter);
        createDateButtons(inflater);

        binding.searchInput.addTextChangedListener(new TextWatcher() {
            @Override public void beforeTextChanged(CharSequence value, int start, int count, int after) {}
            @Override public void onTextChanged(CharSequence value, int start, int before, int count) {
                renderProgramme();
            }
            @Override public void afterTextChanged(Editable value) {}
        });
        binding.clearSearchButton.setOnClickListener(view -> binding.searchInput.setText(""));
        loadMovies();
        return binding.getRoot();
    }

    public void focusSearch() {
        if (binding != null) binding.searchInput.requestFocus();
    }

    private void createDateButtons(LayoutInflater inflater) {
        List<ProgrammeDate> dates = ProgrammeDate.availableDates();
        selectedDateIndex = Math.max(0, Math.min(selectedDateIndex, dates.size() - 1));
        dateButtons.clear();
        binding.programmeDates.removeAllViews();
        for (int index = 0; index < dates.size(); index++) {
            ProgrammeDate date = dates.get(index);
            Button button = (Button) inflater.inflate(
                    R.layout.item_programme_date, binding.programmeDates, false);
            button.setText(date.getLabel());
            button.setContentDescription("Ver sessões de " + date.getLabel());
            final int selected = index;
            button.setOnClickListener(view -> selectDate(selected));
            dateButtons.add(button);
            binding.programmeDates.addView(button);
        }
        updateDateSelection();
    }

    private void selectDate(int index) {
        if (selectedDateIndex == index) return;
        selectedDateIndex = index;
        updateDateSelection();
        renderProgramme();
    }

    private void updateDateSelection() {
        for (int index = 0; index < dateButtons.size(); index++) {
            dateButtons.get(index).setSelected(index == selectedDateIndex);
        }
    }

    private void loadMovies() {
        catalogueLoaded = false;
        state.showLoading();
        ApiClient.get().listMovies("", "", this, new ApiCallback<List<Movie>>() {
            @Override public void onSuccess(List<Movie> movies) {
                if (binding == null) return;
                catalogue.clear();
                catalogue.addAll(movies);
                catalogueLoaded = true;
                renderProgramme();
            }
            @Override public void onError(ApiError error) {
                if (binding != null) state.showError(error.getMessage(), HomeFragment.this::loadMovies);
            }
        });
    }

    private void renderProgramme() {
        if (binding == null || !catalogueLoaded) return;
        final int requestGeneration = ++programmeGeneration;
        String normalized = binding.searchInput.getText().toString().trim().toLowerCase(Locale.ROOT);
        List<Movie> playing = new ArrayList<>();
        List<Movie> upcoming = new ArrayList<>();
        for (int index = 0; index < catalogue.size(); index++) {
            Movie movie = catalogue.get(index);
            if (!matches(movie, normalized)) continue;
            if (index < 2) playing.add(movie); else upcoming.add(movie);
        }
        upcomingAdapter.submit(upcoming);

        if (playing.isEmpty()) {
            programmeAdapter.submit(new ArrayList<>());
            binding.programmeEmpty.setVisibility(upcoming.isEmpty() ? View.GONE : View.VISIBLE);
            if (upcoming.isEmpty()) state.showEmpty(); else state.showContent();
            return;
        }

        state.showLoading();
        ProgrammeDate date = ProgrammeDate.availableDates().get(selectedDateIndex);
        ArrayList<HomeProgramme> results = new ArrayList<>();
        for (int index = 0; index < playing.size(); index++) results.add(null);
        int[] remaining = {playing.size()};
        for (int index = 0; index < playing.size(); index++) {
            Movie movie = playing.get(index);
            int resultIndex = index;
            ApiClient.get().listMovieSessions(movie.getId(), date.getIsoDate(), this,
                    new ApiCallback<List<CinemaSession>>() {
                        @Override public void onSuccess(List<CinemaSession> sessions) {
                            if (!isCurrent(requestGeneration)) return;
                            if (!sessions.isEmpty()) {
                                results.set(resultIndex, new HomeProgramme(movie, sessions));
                            }
                            if (--remaining[0] == 0) showProgramme(results, upcoming);
                        }
                        @Override public void onError(ApiError error) {
                            if (!isCurrent(requestGeneration)) return;
                            programmeGeneration++;
                            state.showError(error.getMessage(), HomeFragment.this::renderProgramme);
                        }
                    });
        }
    }

    private void showProgramme(List<HomeProgramme> results, List<Movie> upcoming) {
        if (binding == null) return;
        ArrayList<HomeProgramme> available = new ArrayList<>();
        for (HomeProgramme programme : results) if (programme != null) available.add(programme);
        programmeAdapter.submit(available);
        binding.programmeEmpty.setVisibility(available.isEmpty() ? View.VISIBLE : View.GONE);
        if (available.isEmpty() && upcoming.isEmpty()) state.showEmpty(); else state.showContent();
    }

    private boolean isCurrent(int generation) {
        return binding != null && generation == programmeGeneration;
    }

    private static boolean matches(Movie movie, String normalized) {
        String genre = movie.getGenres().isEmpty() ? "" : movie.getGenres().get(0);
        return normalized.isEmpty()
                || (movie.getTitle() + " " + genre).toLowerCase(Locale.ROOT).contains(normalized);
    }

    private AppNavigator navigator() {
        return new AppNavigator(NavHostFragment.findNavController(this));
    }

    @Override public void onSaveInstanceState(@NonNull Bundle outState) {
        super.onSaveInstanceState(outState);
        outState.putInt(STATE_SELECTED_DATE, selectedDateIndex);
    }

    @Override public void onDestroyView() {
        programmeGeneration++;
        ApiClient.get().cancel(this);
        dateButtons.clear();
        binding = null;
        super.onDestroyView();
    }
}
