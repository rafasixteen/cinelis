package cinelis.ui.account;

import android.content.Context;
import android.net.Uri;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.EditText;
import android.widget.RadioGroup;

import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.app.AppCompatDelegate;

import cinelis.R;
import cinelis.model.User;
import cinelis.storage.PreferencesManager;

public final class AccountDialogs {
    public interface ProfileListener { void onSave(String name, String email); }
    private AccountDialogs() {}

    public static void editProfile(Context context, User user, ProfileListener listener) {
        View view = LayoutInflater.from(context).inflate(R.layout.dialog_edit_account, null);
        EditText name = view.findViewById(R.id.edit_name);
        EditText email = view.findViewById(R.id.edit_email);
        name.setText(user.getName()); email.setText(user.getEmail());
        new AlertDialog.Builder(context).setTitle(R.string.edit_profile).setView(view)
                .setNegativeButton(android.R.string.cancel, null)
                .setPositiveButton(R.string.save, (dialog, which) -> listener.onSave(
                        name.getText().toString().trim(), email.getText().toString().trim())).show();
    }

    public static void server(AppCompatActivity activity, PreferencesManager preferences) {
        View view = LayoutInflater.from(activity).inflate(R.layout.dialog_server_settings, null);
        EditText input = view.findViewById(R.id.server_url);
        input.setText(preferences.getServerUrl());
        AlertDialog dialog = new AlertDialog.Builder(activity).setTitle(R.string.server_settings).setView(view)
                .setNegativeButton(android.R.string.cancel, null).setPositiveButton(R.string.save, null).create();
        dialog.setOnShowListener(ignored -> dialog.getButton(AlertDialog.BUTTON_POSITIVE).setOnClickListener(button -> {
            Uri uri = Uri.parse(input.getText().toString().trim());
            if (("http".equals(uri.getScheme()) || "https".equals(uri.getScheme())) && uri.getHost() != null) {
                preferences.setServerUrl(input.getText().toString()); dialog.dismiss();
            } else input.setError("Indique um URL http ou https válido.");
        }));
        dialog.show();
    }

    public static void theme(AppCompatActivity activity, PreferencesManager preferences) {
        View view = LayoutInflater.from(activity).inflate(R.layout.dialog_theme, null);
        RadioGroup options = view.findViewById(R.id.theme_options);
        int current = preferences.getThemeMode();
        options.check(current == AppCompatDelegate.MODE_NIGHT_YES ? R.id.theme_dark :
                current == AppCompatDelegate.MODE_NIGHT_NO ? R.id.theme_light : R.id.theme_system);
        new AlertDialog.Builder(activity).setTitle(R.string.appearance).setView(view)
                .setNegativeButton(android.R.string.cancel, null).setPositiveButton(R.string.save, (dialog, which) -> {
                    int mode = options.getCheckedRadioButtonId() == R.id.theme_dark ? AppCompatDelegate.MODE_NIGHT_YES :
                            options.getCheckedRadioButtonId() == R.id.theme_light ? AppCompatDelegate.MODE_NIGHT_NO : AppCompatDelegate.MODE_NIGHT_FOLLOW_SYSTEM;
                    preferences.setThemeMode(mode);
                    AppCompatDelegate.setDefaultNightMode(mode);
                }).show();
    }
}
