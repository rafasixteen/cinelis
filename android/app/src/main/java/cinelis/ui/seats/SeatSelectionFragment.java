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

import cinelis.R;
import cinelis.databinding.FragmentSeatSelectionBinding;
import cinelis.model.CinemaSession;
import cinelis.model.Seat;
import cinelis.navigation.AppNavigator;
import cinelis.network.ApiCallback;
import cinelis.network.ApiClient;
import cinelis.network.ApiError;

public class SeatSelectionFragment extends Fragment {
    private FragmentSeatSelectionBinding binding;
    private SeatGridAdapter adapter;
    private SeatSelectionState selection;
    private String sessionId;

    @Nullable @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container,
                             @Nullable Bundle savedInstanceState) {
        binding = FragmentSeatSelectionBinding.inflate(inflater, container, false);
        adapter = new SeatGridAdapter(this::toggleSeat);
        binding.seatGrid.setLayoutManager(new GridLayoutManager(requireContext(), 8));
        binding.seatGrid.setAdapter(adapter);
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
                adapter.submit(seats, selection);
                binding.loading.setVisibility(View.GONE);
                updateSummary();
            }
            @Override public void onError(ApiError error) { showError(error.getMessage()); }
        });
    }

    private void toggleSeat(Seat seat) {
        if (selection != null && selection.toggle(seat.getId())) {
            adapter.refresh();
            updateSummary();
        }
    }

    private void updateSummary() {
        List<String> labels = selection.selectedLabels();
        binding.selectedLabels.setText(labels.isEmpty() ? "—" : String.join(", ", labels));
        binding.total.setText(String.format(Locale.forLanguageTag("pt-PT"), "%.2f €", selection.total()));
        binding.continueButton.setEnabled(!labels.isEmpty());
        binding.selectionError.setVisibility(View.GONE);
    }

    private void createHold() {
        if (selection == null || selection.selectedSeatIds().isEmpty()) {
            showError(getString(R.string.no_seats_selected));
            return;
        }
        binding.continueButton.setEnabled(false);
        binding.loading.setVisibility(View.VISIBLE);
        ApiClient.get().createSeatHold(sessionId, selection.selectedSeatIds(), this, new ApiCallback<List<Seat>>() {
            @Override public void onSuccess(List<Seat> value) {
                if (binding == null) return;
                new AppNavigator(NavHostFragment.findNavController(SeatSelectionFragment.this))
                        .showCheckout(sessionId, selection.selectedSeatIds());
            }
            @Override public void onError(ApiError error) {
                if (binding == null) return;
                binding.loading.setVisibility(View.GONE);
                binding.continueButton.setEnabled(false);
                showError(error.getStatusCode() == 409 ? "Um dos lugares já não está disponível." : error.getMessage());
            }
        });
    }

    private void showError(String message) {
        if (binding == null) return;
        binding.loading.setVisibility(View.GONE);
        binding.selectionError.setText(message);
        binding.selectionError.setVisibility(View.VISIBLE);
    }

    private static String time(String startsAt) { return startsAt != null && startsAt.length() >= 16 ? startsAt.substring(11, 16) : startsAt; }

    @Override public void onDestroyView() { ApiClient.get().cancel(this); binding = null; super.onDestroyView(); }
}
