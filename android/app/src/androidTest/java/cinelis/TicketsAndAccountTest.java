package cinelis;

import static androidx.test.espresso.Espresso.onView;
import static androidx.test.espresso.Espresso.pressBack;
import static androidx.test.espresso.action.ViewActions.click;
import static androidx.test.espresso.action.ViewActions.scrollTo;
import static androidx.test.espresso.assertion.ViewAssertions.matches;
import static androidx.test.espresso.matcher.ViewMatchers.isDisplayed;
import static androidx.test.espresso.matcher.ViewMatchers.withText;
import static androidx.test.espresso.matcher.ViewMatchers.withId;
import static androidx.test.espresso.matcher.ViewMatchers.withContentDescription;
import static org.hamcrest.Matchers.allOf;

import androidx.test.ext.junit.rules.ActivityScenarioRule;
import androidx.test.ext.junit.runners.AndroidJUnit4;

import org.junit.Rule;
import org.junit.Test;
import org.junit.runner.RunWith;

import cinelis.network.ApiClient;

@RunWith(AndroidJUnit4.class)
public class TicketsAndAccountTest {
    @Rule public ActivityScenarioRule<MainActivity> activityRule = new ActivityScenarioRule<>(MainActivity.class);

    @Test public void ticketsDestinationRequiresAuthentication() {
        activityRule.getScenario().onActivity(activity -> {
            ApiClient.get().clearAuthentication();
            activity.navigateFromBottomBar(R.id.ticketsFragment);
            activity.getSupportFragmentManager().executePendingTransactions();
        });
        onView(withText("Bem-vindo de volta.")).check(matches(isDisplayed()));
    }

    @Test public void cancellingAuthenticationReturnsToHomeAndCanOpenLoginAgain() {
        activityRule.getScenario().onActivity(activity -> {
            ApiClient.get().clearAuthentication();
            activity.navigateFromBottomBar(R.id.ticketsFragment);
        });
        EspressoWait.until(withId(R.id.demo_login));
        pressBack();
        EspressoWait.until(withId(R.id.search_input));
        onView(withId(R.id.search_input)).check(matches(isDisplayed()));
        activityRule.getScenario().onActivity(activity -> activity.navigateFromBottomBar(R.id.accountFragment));
        EspressoWait.until(withId(R.id.demo_login));
        onView(withId(R.id.demo_login)).perform(scrollTo(), click());
        EspressoWait.until(withText("Ana Martins"));
        onView(withText("Ana Martins")).check(matches(isDisplayed()));
    }

    @Test public void demoAccountShowsPurchaseHistoryUpcomingAndPastTicketDetails() {
        activityRule.getScenario().onActivity(activity -> {
            ApiClient.get().clearAuthentication();
            activity.navigateFromBottomBar(R.id.accountFragment);
        });
        EspressoWait.until(withId(R.id.demo_login));
        onView(withId(R.id.demo_login)).perform(scrollTo(), click());
        EspressoWait.until(withText("Ana Martins"));
        EspressoWait.until(withContentDescription("Compra #00000082"));
        onView(withText("ana@exemplo.pt")).check(matches(isDisplayed()));
        activityRule.getScenario().onActivity(activity -> activity.navigateFromBottomBar(R.id.ticketsFragment));
        EspressoWait.until(withText("Entre marés"));
        onView(withText("Entre marés")).perform(click());
        EspressoWait.until(allOf(withId(R.id.ticket_code), withText("CIN-DEMO-2")));
        onView(withId(R.id.ticket_code)).check(matches(withText("CIN-DEMO-2")));
        pressBack();
        EspressoWait.until(withId(R.id.previous_tab));
        onView(withId(R.id.previous_tab)).perform(click());
        EspressoWait.until(withText("A última luz"));
        onView(withText("A última luz")).perform(click());
        EspressoWait.until(allOf(withId(R.id.ticket_code), withText("CIN-DEMO-1")));
        onView(withId(R.id.ticket_code)).check(matches(withText("CIN-DEMO-1")));
    }
}
