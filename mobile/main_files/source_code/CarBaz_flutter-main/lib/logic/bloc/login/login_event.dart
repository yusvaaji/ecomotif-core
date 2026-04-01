part of 'login_bloc.dart';

abstract class LoginEvent extends Equatable {
  const LoginEvent();

  @override
  List<Object?> get props => [];
}

class LoginEventUserEmail extends LoginEvent {
  final String email;

  const LoginEventUserEmail(this.email);

  @override
  List<Object> get props => [email];
}

class LoginEventPassword extends LoginEvent {
  final String password;

  const LoginEventPassword(this.password);

  @override
  List<Object> get props => [password];
}

class SaveUserCredentialEvent extends LoginEvent {
  final bool isActive;

  const SaveUserCredentialEvent(this.isActive);

  @override
  List<Object> get props => [isActive];
}

class ShowPasswordEvent extends LoginEvent {
  final bool show;

  const ShowPasswordEvent(this.show);

  @override
  List<Object> get props => [show];
}

class LoginEventLanguageCode extends LoginEvent {
  final String languageCode;

  const LoginEventLanguageCode(this.languageCode);

  @override
  List<Object> get props => [languageCode];
}

class LoginEventCurrencyIcon extends LoginEvent {
  final String currencyIcon;

  const LoginEventCurrencyIcon(this.currencyIcon);

  @override
  List<Object> get props => [currencyIcon];
}



class LoginEventSubmit extends LoginEvent {
  const LoginEventSubmit();
}

class LoginEventLogout extends LoginEvent {
  const LoginEventLogout();
}

class LoginEventRememberMe extends LoginEvent {}

class LoginEventSaveCredential extends LoginEvent {
  const LoginEventSaveCredential();
}
