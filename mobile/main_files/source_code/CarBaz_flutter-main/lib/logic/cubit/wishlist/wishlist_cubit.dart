import 'package:ecomotif/data/model/home/home_model.dart';
import 'package:ecomotif/logic/cubit/wishlist/wishlist_state_model.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../data/data_provider/remote_url.dart';
import '../../../utils/utils.dart';
import '../../bloc/login/login_bloc.dart';
import '../../repository/wishlist_repository.dart';
import 'wishlist_state.dart';

class WishlistCubit extends Cubit<WishlistStateModel> {
  final WishlistRepository _wishlistRepository;
  final LoginBloc _loginBloc;

  WishlistCubit({
    required WishlistRepository wishlistRepository,
    required LoginBloc loginBloc,
  })  : _wishlistRepository = wishlistRepository,
        _loginBloc = loginBloc,
        super(WishlistStateModel());

  List<FeaturedCars>? wishlistModel;

  Future<void> getWishlist() async {
    if (_loginBloc.userInformation?.accessToken.isNotEmpty ?? false) {
      emit(state.copyWith(wishlistState: WishlistStateLoading()));

      final uri = Utils.tokenWithCode(RemoteUrls.wishLists,
          _loginBloc.userInformation?.accessToken ?? '',
          _loginBloc.state.languageCode);
      final result = await _wishlistRepository.getWishList(uri);
      result.fold((failure) {
        final errorState =
        WishlistStateError(failure.message, failure.statusCode);
        emit(state.copyWith(wishlistState: errorState));
      }, (success) {
        wishlistModel = success;
        final successState = WishlistStateLoaded(success);
        emit(state.copyWith(wishlistState: successState));
      });
    }else{
      const errors = WishlistStateError('', 401);
      emit(state.copyWith(wishlistState: errors));
    }
  }

  Future<void> addToWishlist(String id) async {
    emit(state.copyWith(wishlistState: WishlistStateLoading()));
    final uri = Utils.tokenWithCode(RemoteUrls.addWishLists(id),
        _loginBloc.userInformation!.accessToken, _loginBloc.state.languageCode);
    final result = await _wishlistRepository.addWishList(uri);
    result.fold((failure) {
      final errorState =
          WishlistStateError(failure.message, failure.statusCode);
      emit(state.copyWith(wishlistState: errorState));
    }, (success) {
      final successState = WishListAddedLoaded(success);
      emit(state.copyWith(wishlistState: successState));
    });
  }

  Future<void> removeWishlist(String id) async {
    emit(state.copyWith(wishlistState: WishlistStateLoading()));
    final uri = Utils.tokenWithCode(RemoteUrls.removeWishLists(id),
        _loginBloc.userInformation!.accessToken, _loginBloc.state.languageCode);
    final result = await _wishlistRepository.removeWishList(uri);
    result.fold((failure) {
      final errorState =
          WishlistStateError(failure.message, failure.statusCode);
      emit(state.copyWith(wishlistState: errorState));
    }, (success) {
      wishlistModel?.removeWhere((item) => item.id.toString() == id);
      final successState = WishListRemoveLoaded(success);
      emit(state.copyWith(wishlistState: successState));
    });
  }


  void initPage() {
    emit(state.copyWith(initialPage: 1, isListEmpty: false));
  }
}
