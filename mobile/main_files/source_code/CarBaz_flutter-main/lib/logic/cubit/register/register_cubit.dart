import 'package:ecomotif/data/model/auth/register_state_model.dart';
import 'package:ecomotif/logic/cubit/register/register_state.dart';
import 'package:ecomotif/logic/repository/auth_repository.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../presentation/errors/failure.dart';

class RegisterCubit extends Cubit<RegisterStateModel> {
  final AuthRepository _repository;

  RegisterCubit({
    required AuthRepository authRepository,
  })  : _repository = authRepository,
        super(RegisterStateModel.init());

  void changeName(String text) {
    emit(state.copyWith(name: text, registerState: const RegisterInitial()));
  }

  void changeEmail(String text) {
    emit(state.copyWith(email: text, registerState: const RegisterInitial()));
  }

  void changePassword(String text) {
    emit(
        state.copyWith(password: text, registerState: const RegisterInitial()));
  }

  void changeConPassword(String text) {
    emit(state.copyWith(
        confirmPassword: text, registerState: const RegisterInitial()));
  }

  void showPassword() {
    emit(state.copyWith(
        showPassword: !state.showPassword,
        registerState: const RegisterInitial()));
  }


  void showConfirmPassword() {
    emit(state.copyWith(
        showConPassword: !state.showConPassword,
        registerState: const RegisterInitial()));
  }


  void otpChange(String text) {
    emit(state.copyWith(
      otp: text,
    ));
  }


  Future<void> userRegister() async {
    print('Register: ${state.toMap()}');
    emit(state.copyWith(registerState: RegisterStateLoading()));
    final result = await _repository.signUp(state);
    result.fold(
      (failure) {
        if (failure is InvalidAuthData) {
          final errors = RegisterValidateStateError(failure.errors);
          emit(state.copyWith(registerState: errors));
        } else {
          final errors =
              RegisterStateError(failure.message, failure.statusCode);
          emit(state.copyWith(registerState: errors));
        }
      },
      (success) {
        emit(state.copyWith(registerState: RegisterStateSuccess(success)));
      },
    );
  }


  Future<void> verifyRegOtp() async {
    emit(state.copyWith(registerState: RegisterOtpStateLoading()));
    final result = await _repository.verifyRegOtp(state);
    result.fold(
          (failure) {
        final errors =
        RegisterOtpStateError(failure.message, failure.statusCode);
        emit(state.copyWith(registerState: errors));
      },
          (success) {
        final userLoaded = RegisterOtpStateSuccess(success);
        emit(state.copyWith(registerState: userLoaded));
      },
    );
  }


  Future<void> clearAllField() async {
    emit(RegisterStateModel.init());
  }
}
