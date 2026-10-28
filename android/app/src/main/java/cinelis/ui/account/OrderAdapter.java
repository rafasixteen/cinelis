package cinelis.ui.account;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import java.util.ArrayList;
import java.util.List;
import java.util.Locale;

import cinelis.R;
import cinelis.model.Order;

public final class OrderAdapter extends RecyclerView.Adapter<OrderAdapter.Holder> {
    private final List<Order> orders = new ArrayList<>();
    public void submit(List<Order> values) { orders.clear(); orders.addAll(values); notifyDataSetChanged(); }
    @NonNull @Override public Holder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) { return new Holder(LayoutInflater.from(parent.getContext()).inflate(R.layout.item_order, parent, false)); }
    @Override public void onBindViewHolder(@NonNull Holder holder, int position) { Order order=orders.get(position); holder.title.setText("Compra #" + order.getId().substring(0,8)); holder.meta.setText(order.getCreatedAt().substring(0,10) + " · " + order.getTickets().size() + " bilhete(s) · " + String.format(Locale.forLanguageTag("pt-PT"), "%.2f €", order.getTotal())); }
    @Override public int getItemCount() { return orders.size(); }
    static final class Holder extends RecyclerView.ViewHolder { final TextView title,meta; Holder(View v){super(v);title=v.findViewById(R.id.order_title);meta=v.findViewById(R.id.order_meta);} }
}
