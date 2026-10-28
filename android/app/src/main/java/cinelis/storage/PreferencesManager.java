package cinelis.storage;

import android.content.Context;
import android.content.SharedPreferences;

import androidx.appcompat.app.AppCompatDelegate;

import java.util.Collections;
import java.util.LinkedHashSet;
import java.util.Set;

public final class PreferencesManager {
    public static final String DEFAULT_SERVER_URL = "https://api.cinelis.pt/v1";
    private static final String FILE_NAME = "cinelis_preferences";
    private static final String KEY_THEME_MODE = "theme_mode";
    private static final String KEY_SERVER_URL = "server_url";
    private static final Set<String> PERSISTED_KEYS;

    static {
        LinkedHashSet<String> keys = new LinkedHashSet<>();
        keys.add(KEY_THEME_MODE);
        keys.add(KEY_SERVER_URL);
        PERSISTED_KEYS = Collections.unmodifiableSet(keys);
    }

    private final SharedPreferences preferences;

    public PreferencesManager(Context context) {
        preferences = context.getApplicationContext()
                .getSharedPreferences(FILE_NAME, Context.MODE_PRIVATE);
    }

    public static Set<String> persistedKeys() {
        return PERSISTED_KEYS;
    }

    public String getServerUrl() {
        return preferences.getString(KEY_SERVER_URL, DEFAULT_SERVER_URL);
    }

    public void setServerUrl(String serverUrl) {
        preferences.edit().putString(KEY_SERVER_URL, serverUrl).apply();
    }

    public int getThemeMode() {
        return preferences.getInt(KEY_THEME_MODE, AppCompatDelegate.MODE_NIGHT_FOLLOW_SYSTEM);
    }

    public void setThemeMode(int mode) {
        preferences.edit().putInt(KEY_THEME_MODE, mode).apply();
    }
}
