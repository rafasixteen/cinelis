package cinelis;

import static androidx.test.espresso.Espresso.onView;
import static androidx.test.espresso.action.ViewActions.click;
import static androidx.test.espresso.assertion.ViewAssertions.matches;
import static androidx.test.espresso.matcher.ViewMatchers.isDisplayed;
import static androidx.test.espresso.matcher.ViewMatchers.withText;
import static androidx.test.espresso.matcher.ViewMatchers.withId;
import static androidx.test.espresso.matcher.ViewMatchers.isEnabled;

import androidx.test.ext.junit.rules.ActivityScenarioRule;
import androidx.test.ext.junit.runners.AndroidJUnit4;

import org.junit.Rule;
import org.junit.Test;
import org.junit.runner.RunWith;

import cinelis.navigation.AppNavigator;
import cinelis.network.ApiClient;

@RunWith(AndroidJUnit4.class)
public class PurchaseFlowTest {
    @Rule public ActivityScenarioRule<MainActivity> activityRule =
            new ActivityScenarioRule<>(MainActivity.class);

    @Test public void seatGridUpdatesSelectionSummary() {
        activityRule.getScenario().onActivity(activity -> {
            ApiClient.get().clearAuthentication();
            new AppNavigator(activity.navController()).showSeats(
                    "00000020-0000-4000-8000-000000000020");
            activity.getSupportFragmentManager().executePendingTransactions();
        });
        EspressoWait.until(withText("A2"));
        onView(withText("Escolha os seus lugares")).check(matches(isDisplayed()));
        onView(withText("ECRÃ")).check(matches(isDisplayed()));
        onView(withText("A2")).perform(click());
        activityRule.getScenario().recreate();
        EspressoWait.until(withText("A2"));
        onView(withId(R.id.selected_labels)).check(matches(withText("A2")));
        onView(withId(R.id.continue_button)).check(matches(isEnabled()));
    }
}
