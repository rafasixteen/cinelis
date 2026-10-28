package cinelis.ui.account;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import androidx.fragment.app.Fragment;
import androidx.navigation.NavController;
import androidx.navigation.fragment.NavHostFragment;
import androidx.recyclerview.widget.LinearLayoutManager;

import java.util.List;

import cinelis.R;
import cinelis.databinding.FragmentAccountBinding;
import cinelis.model.Order;
import cinelis.model.User;
import cinelis.navigation.AppNavigator;
import cinelis.network.ApiCallback;
import cinelis.network.ApiClient;
import cinelis.network.ApiError;
import cinelis.storage.PreferencesManager;

public class AccountFragment extends Fragment {
    private FragmentAccountBinding binding;
    private OrderAdapter orderAdapter;
    private User user;
    private boolean reauthRequested;
    @Nullable @Override public View onCreateView(@NonNull LayoutInflater inflater,@Nullable ViewGroup container,@Nullable Bundle state){
        binding=FragmentAccountBinding.inflate(inflater,container,false); orderAdapter=new OrderAdapter();
        binding.ordersList.setLayoutManager(new LinearLayoutManager(requireContext()));binding.ordersList.setAdapter(orderAdapter);
        binding.editProfileButton.setOnClickListener(view -> edit());
        binding.themeButton.setOnClickListener(view -> AccountDialogs.theme((AppCompatActivity)requireActivity(),new PreferencesManager(requireContext())));
        binding.serverButton.setOnClickListener(view -> AccountDialogs.server((AppCompatActivity)requireActivity(),new PreferencesManager(requireContext())));
        binding.logoutButton.setOnClickListener(view -> logout()); binding.deleteButton.setOnClickListener(view -> confirmDelete());
        if(!ApiClient.get().isAuthenticated())new AppNavigator(NavHostFragment.findNavController(this)).showAuth(R.id.accountFragment,null);else load();
        return binding.getRoot();
    }
    private void load(){binding.loading.setVisibility(View.VISIBLE);ApiClient.get().getCurrentUser(this,new ApiCallback<User>(){@Override public void onSuccess(User value){if(binding==null)return;user=value;renderUser();}@Override public void onError(ApiError error){reauth();}});ApiClient.get().listOrders(this,new ApiCallback<List<Order>>(){@Override public void onSuccess(List<Order> values){if(binding==null)return;orderAdapter.submit(values);binding.loading.setVisibility(View.GONE);}@Override public void onError(ApiError error){if(error.getStatusCode()==401)reauth();else if(binding!=null)binding.loading.setVisibility(View.GONE);}});}
    private void renderUser(){binding.accountName.setText(user.getName());binding.accountEmail.setText(user.getEmail());String[] parts=user.getName().trim().split("\\s+");binding.avatar.setText((parts[0].substring(0,1)+(parts.length>1?parts[parts.length-1].substring(0,1):"")).toUpperCase());}
    private void edit(){if(user==null)return;AccountDialogs.editProfile(requireContext(),user,(name,email)->ApiClient.get().updateCurrentUser(name,email,this,new ApiCallback<User>(){@Override public void onSuccess(User value){user=value;renderUser();}@Override public void onError(ApiError error){new AlertDialog.Builder(requireContext()).setMessage(error.getMessage()).setPositiveButton(android.R.string.ok,null).show();}}));}
    private void logout(){ApiClient.get().logout(this,new ApiCallback<Void>(){@Override public void onSuccess(Void value){reauth();}@Override public void onError(ApiError error){reauth();}});}
    private void confirmDelete(){new AlertDialog.Builder(requireContext()).setTitle(R.string.delete_account).setMessage("Esta ação elimina a conta simulada durante esta execução.").setNegativeButton(android.R.string.cancel,null).setPositiveButton(R.string.delete_account,(dialog,which)->ApiClient.get().deleteCurrentUser(this,new ApiCallback<Void>(){@Override public void onSuccess(Void value){reauth();}@Override public void onError(ApiError error){}})).show();}
    private void reauth(){if(!isAdded()||reauthRequested)return;reauthRequested=true;NavController controller=NavHostFragment.findNavController(this);new AppNavigator(controller).showAuth(R.id.accountFragment,null);}
    @Override public void onDestroyView(){ApiClient.get().cancel(this);binding=null;super.onDestroyView();}
}
