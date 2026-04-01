// ignore_for_file: public_member_api_docs, sort_constructors_first
import 'dart:convert';

import 'package:ecomotif/logic/cubit/register/register_state.dart';
import 'package:equatable/equatable.dart';

class RegisterStateModel extends Equatable {
  final String email;
  final String name;
  final String password;
  final String confirmPassword;
  final String otp;
  final String languageCode;
  final bool showPassword;
  final bool showConPassword;
  final RegisterState registerState;

  const RegisterStateModel({
    required this.email,
    required this.name,
    required this.password,
    required this.confirmPassword,
    required this.otp,
    this.languageCode = 'en',
    this.showPassword = true,
    this.showConPassword = true,
    this.registerState = const RegisterInitial(),
  });

  RegisterStateModel copyWith({
    String? email,
    String? name,
    String? password,
    String? confirmPassword,
    String? otp,
    String? languageCode,
    bool? showPassword,
    bool? showConPassword,
    RegisterState? registerState,
  }) {
    return RegisterStateModel(
      email: email ?? this.email,
      name: name ?? this.name,
      password: password ?? this.password,
      confirmPassword: confirmPassword ?? this.confirmPassword,
      otp: otp ?? this.otp,
      languageCode: languageCode ?? this.languageCode,
      showPassword: showPassword ?? this.showPassword,
      showConPassword: showConPassword ?? this.showConPassword,
      registerState: registerState ?? this.registerState,
    );
  }

  Map<String, dynamic> toMap() {
    return <String, dynamic>{
      'email': email,
      'name': name,
      'password': password,
      'password_confirmation': confirmPassword,
      'otp': otp,
    };
  }

  factory RegisterStateModel.fromMap(Map<String, dynamic> map) {
    return RegisterStateModel(
      email: map['email'] ?? '',
      name: map['name'] ?? '',
      password: map['password'] ?? '',
      confirmPassword: map['password_confirmation'] ?? '',
      otp: map['otp'] ?? '',
    );
  }

  RegisterStateModel clear() {
    return const RegisterStateModel(
      email: '',
      name: '',
      password: '',
      confirmPassword: '',
      otp: '',
      showPassword: true,
      showConPassword: true,
      registerState: RegisterInitial(),
    );
  }

  static RegisterStateModel init() {
    return const RegisterStateModel(
      email: '',
      name: '',
      password: '',
      confirmPassword: '',
      otp: '',
      showPassword: true,
      showConPassword: true,
      registerState: RegisterInitial(),
    );
  }

  String toJson() => json.encode(toMap());

  factory RegisterStateModel.fromJson(String source) =>
      RegisterStateModel.fromMap(json.decode(source) as Map<String, dynamic>);

  @override
  bool get stringify => true;

  @override
  List<Object> get props => [
        email,
        name,
        password,
        confirmPassword,
        otp,
        languageCode,
        showPassword,
        showConPassword,
        registerState,
      ];
}
