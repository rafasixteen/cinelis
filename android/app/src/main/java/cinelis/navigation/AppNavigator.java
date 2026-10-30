package cinelis.navigation;

import android.os.Bundle;

import androidx.annotation.IdRes;
import androidx.fragment.app.Fragment;
import androidx.navigation.NavController;
import androidx.navigation.NavOptions;
import androidx.navigation.fragment.NavHostFragment;

import java.util.ArrayList;
import java.util.Arrays;

import cinelis.R;
import cinelis.MainActivity;

public final class AppNavigator {
    public static final String ARG_MOVIE_ID = "movieId";
    public static final String ARG_SESSION_ID = "sessionId";
    public static final String ARG_SEAT_IDS = "seatIds";
    public static final String ARG_TICKET_ID = "ticketId";
    public static final String ARG_RETURN_DESTINATION = "returnDestination";

    private final NavController navController;
    private final MainActivity host;

    public AppNavigator(NavController navController) {
        this.navController = navController;
        this.host = null;
    }

    public AppNavigator(Fragment fragment) {
        this.navController = NavHostFragment.findNavController(fragment);
        this.host = (MainActivity) fragment.requireActivity();
    }

    public void showHome() {
        navigateTopLevel(R.id.homeFragment);
    }

    public void showMovie(String movieId) {
        Bundle args = new Bundle();
        args.putString(ARG_MOVIE_ID, movieId);
        navController.navigate(R.id.movieDetailFragment, args);
    }

    public void showAuth(@IdRes int returnDestination, String sessionId) {
        showAuth(returnDestination, sessionId, null, null);
    }

    public void showAuth(@IdRes int returnDestination, String sessionId,
                         ArrayList<String> seatIds, String ticketId) {
        Bundle args = new Bundle();
        args.putInt(ARG_RETURN_DESTINATION, returnDestination);
        args.putString(ARG_SESSION_ID, sessionId);
        args.putStringArray(ARG_SEAT_IDS, toArray(seatIds));
        args.putString(ARG_TICKET_ID, ticketId);
        if (host == null) throw new IllegalStateException("Authentication requires an activity host");
        host.launchAuthentication(args);
    }

    public void resumeAfterAuthentication(Bundle args) {
        if (args == null) { showHome(); return; }
        int destination = args.getInt(ARG_RETURN_DESTINATION, R.id.accountFragment);
        String sessionId = args.getString(ARG_SESSION_ID);
        String ticketId = args.getString(ARG_TICKET_ID);
        String[] ids = args.getStringArray(ARG_SEAT_IDS);
        if (destination == R.id.seatSelectionFragment && sessionId != null) {
            if (navController.getCurrentDestination() == null
                    || navController.getCurrentDestination().getId() != destination) {
                showSeats(sessionId);
            }
        } else if (destination == R.id.checkoutFragment && sessionId != null) {
            navController.popBackStack(destination, true);
            showCheckout(sessionId, ids == null ? new ArrayList<>() : new ArrayList<>(Arrays.asList(ids)));
        } else if (destination == R.id.ticketFragment && ticketId != null) {
            navController.popBackStack(destination, true);
            showTicket(ticketId);
        } else if (destination == R.id.ticketsFragment) {
            navController.popBackStack(destination, true);
            showTickets();
        } else {
            navController.popBackStack(R.id.accountFragment, true);
            showAccount();
        }
    }

    public void showSeats(String sessionId) {
        Bundle args = new Bundle();
        args.putString(ARG_SESSION_ID, sessionId);
        navController.navigate(R.id.seatSelectionFragment, args);
    }

    public void showCheckout(String sessionId, ArrayList<String> seatIds) {
        Bundle args = new Bundle();
        args.putString(ARG_SESSION_ID, sessionId);
        args.putStringArray(ARG_SEAT_IDS, toArray(seatIds));
        navController.navigate(R.id.checkoutFragment, args);
    }

    public void showTicket(String ticketId) {
        Bundle args = new Bundle();
        args.putString(ARG_TICKET_ID, ticketId);
        navController.navigate(R.id.ticketFragment, args);
    }

    public void showTicketAfterPurchase(String ticketId) {
        Bundle args = new Bundle();
        args.putString(ARG_TICKET_ID, ticketId);
        NavOptions options = new NavOptions.Builder()
                .setPopUpTo(R.id.seatSelectionFragment, true)
                .build();
        navController.navigate(R.id.ticketFragment, args, options);
    }

    public void showTickets() {
        navigateTopLevel(R.id.ticketsFragment);
    }

    public void showAccount() {
        navigateTopLevel(R.id.accountFragment);
    }

    private void navigateTopLevel(@IdRes int destination) {
        if (navController.getCurrentDestination() != null
                && navController.getCurrentDestination().getId() == destination) return;
        if (!navController.popBackStack(destination, false)) {
            navController.navigate(destination);
        }
    }

    private static String[] toArray(ArrayList<String> values) {
        return values == null ? null : values.toArray(new String[0]);
    }
}
