package cinelis.ui.ticket;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;

import com.google.android.material.snackbar.Snackbar;

import cinelis.R;
import cinelis.databinding.FragmentTicketBinding;
import cinelis.model.CinemaSession;
import cinelis.model.Ticket;
import cinelis.navigation.AppNavigator;
import cinelis.network.ApiCallback;
import cinelis.network.ApiClient;
import cinelis.network.ApiError;

public class TicketFragment extends Fragment {
    private FragmentTicketBinding binding;
    private String ticketId;
    @Nullable @Override public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle state) {
        binding = FragmentTicketBinding.inflate(inflater, container, false);
        binding.downloadButton.setOnClickListener(view -> Snackbar.make(view, R.string.download_demo, Snackbar.LENGTH_SHORT).show());
        ticketId = getArguments() == null ? null : getArguments().getString(AppNavigator.ARG_TICKET_ID);
        ApiClient.get().getTicket(ticketId, this, new ApiCallback<Ticket>() {
            @Override public void onSuccess(Ticket ticket) { if (binding != null) show(ticket); }
            @Override public void onError(ApiError error) {
                if (binding == null) return;
                if (error.getStatusCode() == 401) {
                    ApiClient.get().clearAuthentication();
                    new AppNavigator(TicketFragment.this)
                            .showAuth(R.id.ticketFragment, null, null, ticketId);
                    return;
                }
                binding.loading.setVisibility(View.GONE);
                binding.ticketError.setText(error.getMessage());
                binding.ticketError.setVisibility(View.VISIBLE);
            }
        });
        return binding.getRoot();
    }
    private void show(Ticket ticket) {
        CinemaSession session = ticket.getSession();
        binding.loading.setVisibility(View.GONE); binding.ticketContent.setVisibility(View.VISIBLE);
        binding.ticketMovie.setText(session.getMovie() == null ? "Bilhete CineLis" : session.getMovie().getTitle());
        binding.ticketSession.setText(session.getStartsAt().substring(0,10) + " · " + session.getStartsAt().substring(11,16) + " · " + session.getRoomName());
        binding.ticketSeat.setText("Lugar " + ticket.getSeat().getLabel());
        binding.ticketCode.setText(ticket.getCode());
    }
    @Override public void onDestroyView(){ApiClient.get().cancel(this);binding=null;super.onDestroyView();}
}
