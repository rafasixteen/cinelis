package cinelis;

import static org.junit.Assert.assertEquals;
import static org.junit.Assert.assertNotNull;

import androidx.navigation.NavController;
import androidx.navigation.NavDestination;
import androidx.navigation.fragment.NavHostFragment;
import androidx.test.ext.junit.rules.ActivityScenarioRule;
import androidx.test.ext.junit.runners.AndroidJUnit4;

import com.google.android.material.bottomnavigation.BottomNavigationView;

import org.junit.Rule;
import org.junit.Test;
import org.junit.runner.RunWith;

import cinelis.network.ApiClient;

@RunWith(AndroidJUnit4.class)
public class MainNavigationTest {
    @Rule public ActivityScenarioRule<MainActivity> activityRule =
            new ActivityScenarioRule<>(MainActivity.class);

    @Test public void mainActivityHostsSevenFragmentDestinations() {
        activityRule.getScenario().onActivity(activity -> {
            NavHostFragment host = (NavHostFragment) activity.getSupportFragmentManager()
                    .findFragmentById(R.id.nav_host_fragment);
            NavController controller = host.getNavController();
            int destinationCount = 0;
            for (NavDestination ignored : controller.getGraph()) destinationCount++;
            assertEquals(7, destinationCount);
        });
    }

    @Test public void bottomNavigationReachesHomeAndTicketsShell() {
        activityRule.getScenario().onActivity(activity -> {
            ApiClient.get().clearAuthentication();
            NavController controller = activity.navController();
            BottomNavigationView navigation = activity.findViewById(R.id.bottom_navigation);
            assertNotNull(controller.getCurrentDestination());
            activity.navigateFromBottomBar(R.id.homeFragment);
            assertEquals(R.id.homeFragment, controller.getCurrentDestination().getId());
            assertEquals(R.id.homeFragment, navigation.getSelectedItemId());

            activity.navigateFromBottomBar(R.id.ticketsFragment);
            activity.getSupportFragmentManager().executePendingTransactions();
            assertEquals(R.id.ticketsFragment, controller.getCurrentDestination().getId());
        });
    }
}
