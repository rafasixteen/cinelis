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

@RunWith(AndroidJUnit4.class)
public class MainNavigationTest {
    @Rule
    public ActivityScenarioRule<MainActivity> activityRule =
            new ActivityScenarioRule<>(MainActivity.class);

    @Test
    public void navigationGraphContainsEightProductDestinations() {
        activityRule.getScenario().onActivity(activity -> {
            NavHostFragment host = (NavHostFragment) activity
                    .getSupportFragmentManager()
                    .findFragmentById(R.id.nav_host_fragment);
            NavController controller = host.getNavController();
            int destinationCount = 0;
            for (NavDestination ignored : controller.getGraph()) {
                destinationCount++;
            }
            assertEquals(8, destinationCount);
        });
    }

    @Test
    public void bottomNavigationReachesHomeTicketsAndAccount() {
        activityRule.getScenario().onActivity(activity -> {
            NavController controller = activity.navController();
            BottomNavigationView navigation = activity.findViewById(R.id.bottom_navigation);
            assertNotNull(controller.getCurrentDestination());
            assertEquals(R.id.homeFragment, controller.getCurrentDestination().getId());

            navigation.setSelectedItemId(R.id.ticketsFragment);
            assertEquals(R.id.ticketsFragment, controller.getCurrentDestination().getId());

            navigation.setSelectedItemId(R.id.accountFragment);
            assertEquals(R.id.accountFragment, controller.getCurrentDestination().getId());

            navigation.setSelectedItemId(R.id.homeFragment);
            assertEquals(R.id.homeFragment, controller.getCurrentDestination().getId());
        });
    }
}
