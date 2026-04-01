import 'dart:developer';

import 'package:equatable/equatable.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../../data/data_provider/remote_url.dart';
import '../../../data/model/auth/login_state_model.dart';
import '../../../data/model/auth/user_response_model.dart';

import '../../../presentation/errors/errors_model.dart';
import '../../../presentation/errors/failure.dart';
import '../../repository/auth_repository.dart';

part 'login_event.dart';

part 'login_state.dart';

class LoginBloc extends Bloc<LoginEvent, LoginStateModel> {
  final AuthRepository _repository;

  UserResponseModel? _users;

  bool get isLoggedIn => _users != null && _users!.accessToken.isNotEmpty;

  UserResponseModel? get userInformation => _users;

  set saveUserData(UserResponseModel usersData) => _users = usersData;

  LoginBloc({required AuthRepository repository})
      : _repository = repository,
        super(const LoginStateModel()) {
    on<LoginEventUserEmail>((event, emit) {
      emit(state.copyWith(
          email: event.email, loginState: const LoginStateInitial()));
    });
    on<LoginEventPassword>((event, emit) {
      emit(state.copyWith(
          password: event.password, loginState: const LoginStateInitial()));
    });

    on<SaveUserCredentialEvent>((event, emit) {
      emit(state.copyWith(
          isActive: event.isActive, loginState: const LoginStateInitial()));
    });

    on<ShowPasswordEvent>((event, emit) {
      emit(state.copyWith(
          show: !(event.show), loginState: const LoginStateInitial()));
    });


    on<LoginEventLanguageCode>((event, emit) {
      emit(state.copyWith(languageCode: event.languageCode));
    });

    on<LoginEventCurrencyIcon>((event, emit) {
      emit(state.copyWith(currencyIcon: event.currencyIcon));
    });

    on<LoginEventRememberMe>((event, emit) {
      emit(state.copyWith(isActive: !state.isActive));
    });

    on<LoginEventSubmit>(_loginEvent);
    on<LoginEventLogout>(_logoutEvent);
    on<LoginEventSaveCredential>(_saveCredential);

    final result = _repository.getExistingUserInfo();
    result.fold((failure) => _users = null, (success) {
      saveUserData = success;
      log('$success', name: 'saved-user-data');
    });
  }

  Future<void> _saveCredential(LoginEventSaveCredential event, Emitter<LoginStateModel> emit) async {
    try {
      final SharedPreferences pref = await SharedPreferences.getInstance();
      if (state.isActive) {
        pref.setString('email', state.email);
        pref.setString('password', state.password);
      } else {
        final email = pref.getString('email');
        final password = pref.getString('password');
        if(email?.trim().isNotEmpty??false){
          pref.remove('email');
        }
        if(password?.trim().isNotEmpty??false){
          pref.remove('password');
        }
      }
    } catch (e) {
      debugPrint('Error occurring while saving user credentials: ${e.toString()}');
    }
  }

  Future<void> _loginEvent(

      LoginEventSubmit event, Emitter<LoginStateModel> emit) async {
    emit(state.copyWith(loginState: LoginStateLoading()));
    final result = await _repository.login(state);
    result.fold(
      (failure) {
        if (failure is InvalidAuthData) {
          final errors = LoginStateFormValidate(failure.errors);
          emit(state.copyWith(loginState: errors));
        } else {
          final errors = LoginStateError(
              message: failure.message, statusCode: failure.statusCode);
          emit(state.copyWith(loginState: errors));
        }
      },
      (success) {
        final userLoaded = LoginStateLoaded(userResponseModel: success);
        _users = success;

        emit(state.copyWith(loginState: userLoaded));
      },
    );
  }

  Future<void> remoteCredentials() async {
    final SharedPreferences pref = await SharedPreferences.getInstance();
    pref.remove('email');
    pref.remove('password');
  }

  Future<void> _logoutEvent(
      LoginEventLogout event, Emitter<LoginStateModel> emit) async {
    emit(state.copyWith(loginState: LoginStateLogoutLoading()));
    final url = Uri.parse(RemoteUrls.logout).replace(queryParameters: {
      'token': userInformation!.accessToken,
      'lang_code': state.languageCode,
    });
    final result = await _repository.logout(url);
    result.fold(
      (failure) {
        if (failure.statusCode == 500) {
          const loadedData = LoginStateLogoutLoaded('logout success', 200);
          emit(state.copyWith(loginState: loadedData));
        } else {
          final errors =
              LoginStateLogoutError(failure.message, failure.statusCode);
          emit(state.copyWith(loginState: errors));
        }
      },
      (logout) {
        _users = null;
        emit(state.copyWith(loginState: LoginStateLogoutLoaded(logout, 200)));
        //remoteCredentials();
      },
    );
  }
}
