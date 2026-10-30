package cinelis;

import static androidx.test.espresso.Espresso.onView;
import static androidx.test.espresso.action.ViewActions.click;
import static androidx.test.espresso.assertion.ViewAssertions.matches;
import static androidx.test.espresso.matcher.ViewMatchers.isDisplayed;
import static androidx.test.espresso.matcher.ViewMatchers.isSelected;
import static androidx.test.espresso.matcher.ViewMatchers.withContentDescription;
import static androidx.test.espresso.matcher.ViewMatchers.withText;
import static org.hamcrest.Matchers.allOf;

import androidx.test.ext.junit.rules.ActivityScenarioRule;

import org.junit.Rule;
import org.junit.Test;

public class HomeDailySessionsTest {
    @Rule public ActivityScenarioRule<MainActivity> activityRule =
            new ActivityScenarioRule<>(MainActivity.class);

    @Test public void dateProgrammeShowsInlineSessionsAndOpensSeatsDirectly() {
        EspressoWait.until(allOf(withContentDescription("Ver sessões de Hoje"), isDisplayed()));
        onView(withText("Hoje")).check(matches(isSelected()));
        EspressoWait.until(allOf(withContentDescription(
                "Escolher sessão 18:00 de A última luz"), isDisplayed()));

        onView(withContentDescription("Ver sessões de Amanhã")).perform(click());
        onView(withText("Amanhã")).check(matches(isSelected()));
        EspressoWait.until(allOf(withContentDescription(
                "Escolher sessão 17:30 de A última luz"), isDisplayed()));
        onView(withContentDescription("Escolher sessão 17:30 de A última luz")).perform(click());

        EspressoWait.until(allOf(withText("Escolha os seus lugares"), isDisplayed()));
    }
}
