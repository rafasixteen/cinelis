package cinelis.ui.seats;

import static org.junit.Assert.assertEquals;
import static org.junit.Assert.assertFalse;
import static org.junit.Assert.assertTrue;

import org.junit.Test;

import java.util.Arrays;

import cinelis.model.Seat;

public class SeatSelectionStateTest {
    @Test public void availableSeatsToggleAndTotalsRemainInSeatOrder() {
        SeatSelectionState state = new SeatSelectionState(Arrays.asList(
                new Seat("a2", "A", 2, "available"),
                new Seat("a3", "A", 3, "available"),
                new Seat("b3", "B", 3, "sold"),
                new Seat("c1", "C", 1, "held")), 7.5);
        assertTrue(state.toggle("a3"));
        assertTrue(state.toggle("a2"));
        assertFalse(state.toggle("b3"));
        assertFalse(state.toggle("c1"));
        assertEquals(Arrays.asList("A2", "A3"), state.selectedLabels());
        assertEquals(15.0, state.total(), 0.001);
        assertTrue(state.toggle("a2"));
        assertEquals(Arrays.asList("a3"), state.selectedSeatIds());
        assertEquals(7.5, state.total(), 0.001);
    }
}
