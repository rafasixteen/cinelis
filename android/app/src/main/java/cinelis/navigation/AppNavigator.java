package cinelis.navigation;

import android.os.Bundle;

import androidx.annotation.IdRes;
import androidx.navigation.NavController;

import java.util.ArrayList;

import cinelis.R;

public final class AppNavigator {
    public static final String ARG_MOVIE_ID = "movieId";
    public static final String ARG_SESSION_ID = "sessionId";
    public static final String ARG_SEAT_IDS = "seatIds";
    public static final String ARG_TICKET_ID = "ticketId";
    public static final String ARG_RETURN_DESTINATION = "returnDestination";

    private final NavController navController;

    public AppNavigator(NavController navController) {
        this.navController = navController;
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
        Bundle args = new Bundle();
        args.putInt(ARG_RETURN_DESTINATION, returnDestination);
        args.putString(ARG_SESSION_ID, sessionId);
        navController.navigate(R.id.authFragment, args);
    }

    public void showSeats(String sessionId) {
        Bundle args = new Bundle();
        args.putString(ARG_SESSION_ID, sessionId);
        navController.navigate(R.id.seatSelectionFragment, args);
    }

    public void showCheckout(String sessionId, ArrayList<String> seatIds) {
        Bundle args = new Bundle();
        args.putString(ARG_SESSION_ID, sessionId);
        args.putStringArrayList(ARG_SEAT_IDS, seatIds);
        navController.navigate(R.id.checkoutFragment, args);
    }

    public void showTicket(String ticketId) {
        Bundle args = new Bundle();
        args.putString(ARG_TICKET_ID, ticketId);
        navController.navigate(R.id.ticketFragment, args);
    }

    public void showTickets() {
        navigateTopLevel(R.id.ticketsFragment);
    }

    public void showAccount() {
        navigateTopLevel(R.id.accountFragment);
    }

    private void navigateTopLevel(@IdRes int destination) {
        if (!navController.popBackStack(destination, false)) {
            navController.navigate(destination);
        }
    }
}
