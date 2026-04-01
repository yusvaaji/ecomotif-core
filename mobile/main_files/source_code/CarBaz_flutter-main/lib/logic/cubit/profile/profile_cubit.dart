import 'package:ecomotif/logic/cubit/profile/profile_state.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../data/data_provider/remote_url.dart';
import '../../../data/model/auth/user_response_model.dart';
import '../../../presentation/errors/failure.dart';
import '../../../utils/utils.dart';
import '../../bloc/login/login_bloc.dart';
import '../../repository/user_dashboard_repository.dart';

class ProfileCubit extends Cubit<User> {
  final UserDashboardRepository _repository;
  final LoginBloc _loginBloc;

  ProfileCubit({
    required UserDashboardRepository repository,
    required LoginBloc loginBloc,
  })  : _repository = repository,
        _loginBloc = loginBloc,
        super(User.init());

  User? user;

  void nameChange(String text) {
    emit(state.copyWith(
      name: text,
    ));
  }

  void emailChange(String text) {
    emit(state.copyWith(
      email: text,
    ));
  }
  void phoneChange(String text) {
    emit(state.copyWith(
      phone: text,
    ));
  }

  void designationChange(String text) {
    emit(state.copyWith(
      designation: text,
    ));
  }

  void addressChange(String text) {
    emit(state.copyWith(
      address: text,
    ));
  }

  void imageChange(String text) {
    emit(state.copyWith(
      image: text,
    ));
  }

  void bannerImageChange(String text) {
    emit(state.copyWith(
      bannerImage: text,
    ));
  }

  Future<void> getProfileInfo() async {
    if (_loginBloc.userInformation?.accessToken.isNotEmpty ?? false) {
      emit(state.copyWith(profileState: GetProfileDataStateLoading()));
      final uri = Utils.tokenWithCode(
        RemoteUrls.getProfileData,
        _loginBloc.userInformation!.accessToken,
        _loginBloc.state.languageCode,
      );
      final result = await _repository.getProfileData(uri);
      result.fold((failure) {
        final errorState =
        GetProfileDataStateError(failure.message, failure.statusCode);
        emit(state.copyWith(profileState: errorState));
      }, (success) {
        user = success;
        if (user != null) {
          emit(state.copyWith(name: user!.name));
          emit(state.copyWith(email: user!.email));
          emit(state.copyWith(phone: user!.phone));
          emit(state.copyWith(address: user!.address));
          emit(state.copyWith(designation: user!.designation));
          emit(state.copyWith(tempImage: user!.image));
          emit(state.copyWith(bannerTempImage: user!.bannerImage));
        }
        final successState = GetProfileDataStateLoaded(success);
        emit(state.copyWith(profileState: successState));
      });
    }
    else{
      const errors = GetProfileDataStateError('', 401);
      emit(state.copyWith(profileState: errors));
    }
  }

  Future<void> updateUserInfo() async {
    print("update body ${state.toMap()}");
    emit(state.copyWith(profileState: UpdateProfileStateLoading()));
    final uri = Utils.tokenWithQuery(RemoteUrls.updateProfile,
        _loginBloc.userInformation!.accessToken, _loginBloc.state.languageCode, extraParams: {'_method':'PUT'});
    final result = await _repository.updateProfileInfo(state, uri);
    result.fold((failure) {
      if (failure is InvalidAuthData) {
        final errors = UpdateProfileStateFormValidate(failure.errors);
        emit(state.copyWith(profileState: errors));
      } else {
        final errors =
            UpdateProfileStateError(failure.message, failure.statusCode);
        emit(state.copyWith(profileState: errors));
      }
    }, (success) {
      final loaded = UpdateProfileStateLoaded(success);
      emit(state.copyWith(profileState: loaded));
    });
  }
}
