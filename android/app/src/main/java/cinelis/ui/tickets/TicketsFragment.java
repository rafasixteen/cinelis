package cinelis.ui.tickets;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.navigation.fragment.NavHostFragment;
import androidx.recyclerview.widget.LinearLayoutManager;

import java.util.List;

import cinelis.R;
import cinelis.databinding.FragmentTicketsBinding;
import cinelis.model.Ticket;
import cinelis.navigation.AppNavigator;
import cinelis.network.ApiCallback;
import cinelis.network.ApiClient;
import cinelis.network.ApiError;

public class TicketsFragment extends Fragment {
    private FragmentTicketsBinding binding;
    private TicketAdapter adapter;
    private boolean upcoming = true;
    @Nullable @Override public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle state) {
        binding=FragmentTicketsBinding.inflate(inflater,container,false);
        if(state!=null)upcoming=state.getBoolean("upcoming",true);
        adapter=new TicketAdapter(ticket -> new AppNavigator(NavHostFragment.findNavController(this)).showTicket(ticket.getId()));
        binding.ticketsList.setLayoutManager(new LinearLayoutManager(requireContext())); binding.ticketsList.setAdapter(adapter);
        binding.upcomingTab.setOnClickListener(view -> load(true)); binding.previousTab.setOnClickListener(view -> load(false));
        if (!ApiClient.get().isAuthenticated()) new AppNavigator(NavHostFragment.findNavController(this)).showAuth(R.id.ticketsFragment,null);
        else load(upcoming);
        return binding.getRoot();
    }
    private void load(boolean upcoming){this.upcoming=upcoming;binding.loading.setVisibility(View.VISIBLE);binding.emptyMessage.setVisibility(View.GONE);ApiClient.get().listTickets(upcoming,this,new ApiCallback<List<Ticket>>(){@Override public void onSuccess(List<Ticket> values){if(binding==null)return;binding.loading.setVisibility(View.GONE);adapter.submit(values);binding.emptyMessage.setVisibility(values.isEmpty()?View.VISIBLE:View.GONE);}@Override public void onError(ApiError error){if(binding==null)return;binding.loading.setVisibility(View.GONE);binding.emptyMessage.setText(error.getMessage());binding.emptyMessage.setVisibility(View.VISIBLE);}});}
    @Override public void onSaveInstanceState(@NonNull Bundle outState){super.onSaveInstanceState(outState);outState.putBoolean("upcoming",upcoming);}
    @Override public void onDestroyView(){ApiClient.get().cancel(this);binding=null;super.onDestroyView();}
}
