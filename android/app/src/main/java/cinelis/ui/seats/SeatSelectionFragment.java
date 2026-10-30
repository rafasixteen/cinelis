package cinelis.ui.seats;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.navigation.fragment.NavHostFragment;
import androidx.recyclerview.widget.GridLayoutManager;

import java.util.List;
import java.util.Locale;
import java.util.ArrayList;

import cinelis.R;
import cinelis.databinding.FragmentSeatSelectionBinding;
import cinelis.model.CinemaSession;
import cinelis.model.Seat;
import cinelis.navigation.AppNavigator;
import cinelis.network.ApiCallback;
import cinelis.network.ApiClient;
import cinelis.network.ApiError;

public class SeatSelectionFragment extends Fragment {
    private static final String STATE_SELECTED_SEAT_IDS = "selectedSeatIds";
    private FragmentSeatSelectionBinding binding;
    private SeatGridAdapter adapter;
    private SeatSelectionState selection;
    private String sessionId;
    private ArrayList<String> restoredSeatIds = new ArrayList<>();
    private boolean submitting;
    private float zoom = 1f;

    @Nullable @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container,
                             @Nullable Bundle savedInstanceState) {
        binding = FragmentSeatSelectionBinding.inflate(inflater, container, false);
        submitting = false;
        if (getArguments() != null) {
            ArrayList<String> argumentSeatIds = getArguments()
                    .getStringArrayList(STATE_SELECTED_SEAT_IDS);
            if (argumentSeatIds != null) restoredSeatIds = argumentSeatIds;
        }
        if (savedInstanceState != null) {
            ArrayList<String> savedSeatIds = savedInstanceState
                    .getStringArrayList(STATE_SELECTED_SEAT_IDS);
            if (savedSeatIds != null) restoredSeatIds = savedSeatIds;
        }
        if (restoredSeatIds == null) restoredSeatIds = new ArrayList<>();
        adapter = new SeatGridAdapter(this::toggleSeat);
        if (savedInstanceState != null) zoom = savedInstanceState.getFloat("zoom", 1f);
        binding.seatGrid.setLayoutManager(new GridLayoutManager(requireContext(), 8));
        binding.seatGrid.setAdapter(adapter);
        binding.zoomIn.setOnClickListener(view -> setZoom(Math.min(2f, zoom + 0.25f)));
        binding.zoomOut.setOnClickListener(view -> setZoom(Math.max(1f, zoom - 0.25f)));
        setZoom(zoom);
        binding.continueButton.setOnClickListener(view -> createHold());
        sessionId = getArguments() == null ? null : getArguments().getString(AppNavigator.ARG_SESSION_ID);
        load();
        return binding.getRoot();
    }

    private void load() {
        binding.loading.setVisibility(View.VISIBLE);
        ApiClient.get().getSession(sessionId, this, new ApiCallback<CinemaSession>() {
            @Override public void onSuccess(CinemaSession session) {
                if (binding == null) return;
                String movie = session.getMovie() == null ? "Sessão CineLis" : session.getMovie().getTitle();
                binding.sessionSummary.setText(movie + " · " + session.getRoomName() + " · " + time(session.getStartsAt()));
                loadSeats(session.getPrice());
            }
            @Override public void onError(ApiError error) { showError(error.getMessage()); }
        });
    }

    private void loadSeats(double price) {
        ApiClient.get().getSessionSeats(sessionId, this, new ApiCallback<List<Seat>>() {
            @Override public void onSuccess(List<Seat> seats) {
                if (binding == null) return;
                selection = new SeatSelectionState(seats, price);
                for (String id : restoredSeatIds) selection.restore(id);
                adapter.submit(seats, selection);
                binding.loading.setVisibility(View.GONE);
                updateSummary();
            }
            @Override public void onError(ApiError error) { showError(error.getMessage()); }
        });
    }

    private void toggleSeat(Seat seat) {
        if (!submitting && selection != null && selection.toggle(seat.getId())) {
            adapter.refresh();
            updateSummary();
        }
    }

    private void updateSummary() {
        List<String> labels = selection.selectedLabels();
        binding.selectedLabels.setText(labels.isEmpty() ? "—" : String.join(", ", labels));
        binding.total.setText(String.format(Locale.forLanguageTag("pt-PT"), "%.2f €", selection.total()));
        binding.continueButton.setEnabled(!submitting && !labels.isEmpty());
        binding.selectionError.setVisibility(View.GONE);
    }

    private void createHold() {
        if (selection == null || selection.selectedSeatIds().isEmpty()) {
            showError(getString(R.string.no_seats_selected));
            return;
        }
        final ArrayList<String> submittedSeatIds = new ArrayList<>(selection.selectedSeatIds());
        submitting = true;
        adapter.setInteractionEnabled(false);
        binding.continueButton.setEnabled(false);
        binding.loading.setVisibility(View.VISIBLE);
        ApiClient.get().createSeatHold(sessionId, submittedSeatIds, this, new ApiCallback<List<Seat>>() {
            @Override public void onSuccess(List<Seat> value) {
                if (binding == null) return;
                restoredSeatIds = new ArrayList<>(submittedSeatIds);
                if (getArguments() != null) {
                    getArguments().putStringArrayList(STATE_SELECTED_SEAT_IDS,
                            new ArrayList<>(submittedSeatIds));
                }
                new AppNavigator(NavHostFragment.findNavController(SeatSelectionFragment.this))
                        .showCheckout(sessionId, submittedSeatIds);
            }
            @Override public void onError(ApiError error) {
                if (binding == null) return;
                submitting = false;
                adapter.setInteractionEnabled(true);
                binding.loading.setVisibility(View.GONE);
                updateSummary();
                if (error.getStatusCode() == 401) {
                    ApiClient.get().clearAuthentication();
                    new AppNavigator(SeatSelectionFragment.this)
                            .showAuth(R.id.seatSelectionFragment, sessionId);
                    return;
                }
                showError(error.getStatusCode() == 409 ? "Um dos lugares já não está disponível." : error.getMessage());
            }
        });
    }

    private void setZoom(float value) {
        zoom = value;
        float density = getResources().getDisplayMetrics().density;
        binding.seatGrid.getLayoutParams().width = Math.round(304 * zoom * density);
        binding.seatGrid.requestLayout();
        for (int i = 0; i < binding.rowLabels.getChildCount(); i++) {
            binding.rowLabels.getChildAt(i).getLayoutParams().height = Math.round(38 * zoom * density);
        }
        binding.rowLabels.requestLayout();
        adapter.setZoom(zoom);
        binding.zoomOut.setEnabled(zoom > 1f);
        binding.zoomIn.setEnabled(zoom < 2f);
    }

    private void showError(String message) {
        if (binding == null) return;
        binding.loading.setVisibility(View.GONE);
        binding.selectionError.setText(message);
        binding.selectionError.setVisibility(View.VISIBLE);
    }

    private static String time(String startsAt) { return startsAt != null && startsAt.length() >= 16 ? startsAt.substring(11, 16) : startsAt; }

    @Override public void onSaveInstanceState(@NonNull Bundle outState) {
        super.onSaveInstanceState(outState);
        outState.putFloat("zoom", zoom);
        if (selection != null) {
            outState.putStringArrayList(STATE_SELECTED_SEAT_IDS, selection.selectedSeatIds());
        }
    }

    @Override public void onDestroyView() { ApiClient.get().cancel(this); binding = null; super.onDestroyView(); }
}
