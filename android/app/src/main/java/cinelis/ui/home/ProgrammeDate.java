package cinelis.ui.home;

import java.util.Arrays;
import java.util.Collections;
import java.util.List;

public final class ProgrammeDate {
    private final String isoDate;
    private final String label;

    private ProgrammeDate(String isoDate, String label) {
        this.isoDate = isoDate;
        this.label = label;
    }

    public String getIsoDate() { return isoDate; }
    public String getLabel() { return label; }

    public static List<ProgrammeDate> availableDates() {
        return Collections.unmodifiableList(Arrays.asList(
                new ProgrammeDate("2026-10-28", "Hoje"),
                new ProgrammeDate("2026-10-29", "Amanhã"),
                new ProgrammeDate("2026-10-30", "30 out.")));
    }
}
