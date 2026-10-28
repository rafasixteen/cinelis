package cinelis;

import static androidx.test.espresso.Espresso.onView;
import static androidx.test.espresso.action.ViewActions.click;
import static androidx.test.espresso.assertion.ViewAssertions.matches;
import static androidx.test.espresso.matcher.ViewMatchers.isDisplayed;
import static androidx.test.espresso.matcher.ViewMatchers.withText;

import androidx.test.ext.junit.rules.ActivityScenarioRule;
import androidx.test.ext.junit.runners.AndroidJUnit4;

import org.junit.Rule;
import org.junit.Test;
import org.junit.runner.RunWith;

import cinelis.navigation.AppNavigator;

@RunWith(AndroidJUnit4.class)
public class PurchaseFlowTest {
    @Rule public ActivityScenarioRule<MainActivity> activityRule =
            new ActivityScenarioRule<>(MainActivity.class);

    @Test public void seatGridUpdatesSelectionSummary() throws Exception {
        activityRule.getScenario().onActivity(activity -> {
            new AppNavigator(activity.navController()).showSeats(
                    "00000020-0000-4000-8000-000000000020");
            activity.getSupportFragmentManager().executePendingTransactions();
        });
        Thread.sleep(700L);
        onView(withText("Escolha os seus lugares")).check(matches(isDisplayed()));
        onView(withText("ECRÃ")).check(matches(isDisplayed()));
        onView(withText("A1")).perform(click());
        onView(withText("7,50 €")).check(matches(isDisplayed()));
    }
}
