package cinelis;

import static androidx.test.espresso.Espresso.onView;
import static androidx.test.espresso.action.ViewActions.clearText;
import static androidx.test.espresso.action.ViewActions.closeSoftKeyboard;
import static androidx.test.espresso.action.ViewActions.replaceText;
import static androidx.test.espresso.assertion.ViewAssertions.matches;
import static androidx.test.espresso.matcher.ViewMatchers.isDisplayed;
import static androidx.test.espresso.matcher.ViewMatchers.withId;
import static androidx.test.espresso.matcher.ViewMatchers.withText;

import androidx.test.ext.junit.rules.ActivityScenarioRule;
import androidx.test.ext.junit.runners.AndroidJUnit4;

import org.junit.Rule;
import org.junit.Test;
import org.junit.runner.RunWith;

@RunWith(AndroidJUnit4.class)
public class CompletePrototypeFlowTest {
    @Rule public ActivityScenarioRule<MainActivity> activityRule = new ActivityScenarioRule<>(MainActivity.class);

    @Test public void catalogueSearchAndProtectedAccountFormOneContinuousFlow() throws Exception {
        Thread.sleep(600L);
        onView(withId(R.id.search_input)).perform(replaceText("filme inexistente"), closeSoftKeyboard());
        onView(withText("Não encontrámos filmes para esta pesquisa.")).check(matches(isDisplayed()));
        onView(withId(R.id.search_input)).perform(clearText(), closeSoftKeyboard());
        activityRule.getScenario().onActivity(activity -> {
            activity.navigateFromBottomBar(R.id.accountFragment);
            activity.getSupportFragmentManager().executePendingTransactions();
        });
        Thread.sleep(300L);
        onView(withText("Bem-vindo de volta.")).check(matches(isDisplayed()));
    }
}
