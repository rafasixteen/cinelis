package cinelis.storage;

import static org.junit.Assert.assertEquals;

import java.util.LinkedHashSet;
import java.util.Set;

import org.junit.Test;

public class PreferencePolicyTest {
    @Test
    public void onlyThemeAndServerUrlArePersisted() {
        Set<String> expected = new LinkedHashSet<>();
        expected.add("theme_mode");
        expected.add("server_url");

        assertEquals(expected, PreferencesManager.persistedKeys());
    }
}
