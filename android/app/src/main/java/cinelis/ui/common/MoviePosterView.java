package cinelis.ui.common;

import android.content.Context;
import android.graphics.Canvas;
import android.graphics.Color;
import android.graphics.Paint;
import android.graphics.RectF;
import android.util.AttributeSet;
import android.view.View;
import androidx.annotation.Nullable;
import androidx.core.content.res.ResourcesCompat;
import java.util.Locale;
import cinelis.R;
import cinelis.model.Movie;

/** Native rendering of the abstract poster artwork in the editable mobile mockups. */
public final class MoviePosterView extends View {
    private final Paint paint = new Paint(Paint.ANTI_ALIAS_FLAG);
    private String title = "";
    public MoviePosterView(Context context, @Nullable AttributeSet attrs) {
        super(context, attrs);
        paint.setTypeface(ResourcesCompat.getFont(context, R.font.inter_regular));
    }
    public void setMovie(Movie movie) {
        title = movie.getTitle();
        invalidate();
    }
    @Override protected void onDraw(Canvas canvas) {
        super.onDraw(canvas);
        float d = getResources().getDisplayMetrics().density;
        float w = getWidth(), h = getHeight();
        paint.setColor(Color.rgb(45, 42, 37));
        canvas.drawRoundRect(new RectF(0, 0, w, h), 8*d, 8*d, paint);
        float cx = w/2, cy = h*0.35f;
        paint.setColor(Color.rgb(94, 234, 212));
        if (title.contains("marés") || title.contains("lado")) {
            for (int i=0; i<4; i++) {
                paint.setColor(i == 1 ? Color.rgb(94,234,212) : Color.rgb(69,65,55));
                canvas.drawRect(w*0.1f, h*0.14f+i*23*d, w*0.9f, h*0.14f+i*23*d+8*d, paint);
            }
        } else if (title.contains("Órbita")) {
            paint.setStyle(Paint.Style.STROKE); paint.setStrokeWidth(3*d);
            canvas.drawOval(new RectF(w*0.16f,h*0.14f,w*0.84f,h*0.56f),paint);
            paint.setStyle(Paint.Style.FILL);
            canvas.drawCircle(cx,cy,12*d,paint);
        } else {
            canvas.drawCircle(cx,cy,w*0.26f,paint);
            paint.setColor(Color.rgb(33,31,26));
            canvas.drawRect(w*0.08f,h*0.51f,w*0.92f,h*0.59f,paint);
            canvas.drawRect(w*0.18f,h*0.61f,w*0.82f,h*0.67f,paint);
        }
        paint.setColor(Color.rgb(182,176,165)); paint.setTextSize(10.24f*d);
        canvas.drawText("C I N E L I S",12*d,h-46*d,paint);
        paint.setColor(Color.rgb(241,240,238)); paint.setTextSize(12.8f*d);
        String text = title.toUpperCase(Locale.forLanguageTag("pt-PT"));
        while (paint.measureText(text)>w-24*d && text.length()>1) text=text.substring(0,text.length()-1);
        canvas.drawText(text,12*d,h-22*d,paint);
    }
}
