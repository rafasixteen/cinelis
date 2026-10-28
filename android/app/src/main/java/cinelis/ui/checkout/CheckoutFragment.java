package cinelis.ui.checkout;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.navigation.fragment.NavHostFragment;

import java.util.ArrayList;
import java.util.List;
import java.util.Locale;

import cinelis.R;
import cinelis.databinding.FragmentCheckoutBinding;
import cinelis.model.CinemaSession;
import cinelis.model.Order;
import cinelis.model.Seat;
import cinelis.navigation.AppNavigator;
import cinelis.network.ApiCallback;
import cinelis.network.ApiClient;
import cinelis.network.ApiError;

public class CheckoutFragment extends Fragment {
    private FragmentCheckoutBinding binding;
    private String sessionId;
    private ArrayList<String> seatIds;
    private CinemaSession session;

    @Nullable @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container,
                             @Nullable Bundle savedInstanceState) {
        binding = FragmentCheckoutBinding.inflate(inflater, container, false);
        Bundle args = getArguments();
        sessionId = args == null ? null : args.getString(AppNavigator.ARG_SESSION_ID);
        seatIds = args == null ? new ArrayList<>() : args.getStringArrayList(AppNavigator.ARG_SEAT_IDS);
        if (seatIds == null) seatIds = new ArrayList<>();
        binding.payButton.setOnClickListener(view -> pay());
        loadSummary();
        return binding.getRoot();
    }

    private void loadSummary() {
        ApiClient.get().getSession(sessionId, this, new ApiCallback<CinemaSession>() {
            @Override public void onSuccess(CinemaSession value) {
                if (binding == null) return;
                session = value;
                binding.checkoutMovie.setText(value.getMovie() == null ? "CineLis" : value.getMovie().getTitle());
                binding.checkoutSession.setText(value.getRoomName() + " · " + value.getStartsAt().substring(0, 10) + " · " + value.getStartsAt().substring(11, 16));
                binding.checkoutTotal.setText(String.format(Locale.forLanguageTag("pt-PT"), "%.2f €", value.getPrice() * seatIds.size()));
                loadSeatLabels();
            }
            @Override public void onError(ApiError error) { showError(error.getMessage()); }
        });
    }

    private void loadSeatLabels() {
        ApiClient.get().getSessionSeats(sessionId, this, new ApiCallback<List<Seat>>() {
            @Override public void onSuccess(List<Seat> seats) {
                if (binding == null) return;
                List<String> labels = new ArrayList<>();
                for (Seat seat : seats) if (seatIds.contains(seat.getId())) labels.add(seat.getLabel());
                binding.checkoutSeats.setText("Lugares " + String.join(", ", labels));
            }
            @Override public void onError(ApiError error) { showError(error.getMessage()); }
        });
    }

    private void pay() {
        String phone = binding.phoneInput.getText().toString().replace(" ", "");
        if (!phone.matches("9[1236]\\d{7}")) {
            showError(getString(R.string.invalid_phone));
            return;
        }
        binding.checkoutError.setVisibility(View.GONE);
        binding.payButton.setEnabled(false);
        binding.loading.setVisibility(View.VISIBLE);
        ApiClient.get().createOrder(sessionId, seatIds, this, new ApiCallback<Order>() {
            @Override public void onSuccess(Order order) {
                if (binding == null || order.getTickets().isEmpty()) return;
                new AppNavigator(NavHostFragment.findNavController(CheckoutFragment.this))
                        .showTicket(order.getTickets().get(0).getId());
            }
            @Override public void onError(ApiError error) {
                if (binding == null) return;
                binding.loading.setVisibility(View.GONE);
                binding.payButton.setEnabled(true);
                showError(error.getMessage());
            }
        });
    }

    private void showError(String message) {
        if (binding == null) return;
        binding.checkoutError.setText(message);
        binding.checkoutError.setVisibility(View.VISIBLE);
    }

    @Override public void onDestroyView() { ApiClient.get().cancel(this); binding = null; super.onDestroyView(); }
}
