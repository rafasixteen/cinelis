package cinelis;

import static androidx.test.espresso.Espresso.onView;
import static androidx.test.espresso.assertion.ViewAssertions.matches;
import static androidx.test.espresso.matcher.ViewMatchers.isDisplayed;
import static androidx.test.espresso.matcher.ViewMatchers.withText;

import androidx.test.ext.junit.rules.ActivityScenarioRule;
import androidx.test.ext.junit.runners.AndroidJUnit4;

import org.junit.Rule;
import org.junit.Test;
import org.junit.runner.RunWith;

@RunWith(AndroidJUnit4.class)
public class TicketsAndAccountTest {
    @Rule public ActivityScenarioRule<MainActivity> activityRule = new ActivityScenarioRule<>(MainActivity.class);

    @Test public void accountAndTicketsDestinationsExposePortugueseHeadings() {
        activityRule.getScenario().onActivity(activity -> {
            activity.navigateFromBottomBar(R.id.ticketsFragment);
            activity.getSupportFragmentManager().executePendingTransactions();
        });
        onView(withText("Os meus bilhetes")).check(matches(isDisplayed()));
    }
}
