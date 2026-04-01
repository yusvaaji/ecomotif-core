// ignore_for_file: public_member_api_docs, sort_constructors_first
import 'dart:convert';

import 'package:ecomotif/logic/cubit/profile/profile_state.dart';
import 'package:equatable/equatable.dart';

class UserResponseModel extends Equatable {
  final String accessToken;
  final String tokenType;
  final int expiresIn;
  final User? user;
  final String userType;
  const UserResponseModel({
    required this.accessToken,
    required this.tokenType,
    required this.expiresIn,
    this.user,
    required this.userType,
  });

  UserResponseModel copyWith({
    String? accessToken,
    String? tokenType,
    int? expiresIn,
    User? user,
    String? userType,
  }) {
    return UserResponseModel(
      accessToken: accessToken ?? this.accessToken,
      tokenType: tokenType ?? this.tokenType,
      expiresIn: expiresIn ?? this.expiresIn,
      user: user ?? this.user,
      userType: userType ?? this.userType,
    );
  }

  Map<String, dynamic> toMap() {
    return <String, dynamic>{
      'access_token': accessToken,
      'token_type': tokenType,
      'expires_in': expiresIn,
      'user': user?.toMap(),
      'user_type': userType,
    };
  }

  factory UserResponseModel.fromMap(Map<String, dynamic> map) {
    return UserResponseModel(
      accessToken: map['access_token']?? '',
      tokenType: map['token_type']?? '',
      expiresIn: map['expires_in'] ?? 0,
      user: map['user'] != null ? User.fromMap(map['user'] as Map<String,dynamic>) : null,
      userType: map['user_type'] ?? '',
    );
  }

  String toJson() => json.encode(toMap());

  factory UserResponseModel.fromJson(String source) => UserResponseModel.fromMap(json.decode(source) as Map<String, dynamic>);

  @override
  bool get stringify => true;

  @override
  List<Object> get props {
    return [
      accessToken,
      tokenType,
      expiresIn,
      user!,
      userType,
    ];
  }
}

class User extends Equatable {
  final int id;
  final String username;
  final String name;
  final String image;
  final String tempImage;
  final String bannerImage;
  final String bannerTempImage;
  final String status;
  final String email;
  final String isBanned;
  final int isDealer;
  final String designation;
  final String address;
  final String phone;
  final int totalCar;
  final ProfileState profileState;

  const User({
     this.id = 0,
     this.username = '',
     this.name = '',
     this.image = '',
     this.tempImage = '',
     this.bannerImage = '',
     this.bannerTempImage = '',
     this.status = '',
     this.email = '',
     this.isBanned = '',
     this.isDealer = 0,
     this.designation = '',
     this.address = '',
     this.phone = '',
     this.totalCar = 0,
    this.profileState = const ProfileInitial(),
  });

  User copyWith({
    int? id,
    String? username,
    String? name,
    String? image,
    String? tempImage,
    String? bannerImage,
    String? bannerTempImage,
    String? status,
    String? email,
    String? isBanned,
    int? isDealer,
    String? designation,
    String? address,
    String? phone,
    int? totalCar,
    ProfileState? profileState,
  }) {
    return User(
      id: id ?? this.id,
      username: username ?? this.username,
      name: name ?? this.name,
      image: image ?? this.image,
      tempImage: tempImage ?? this.tempImage,
      bannerImage: bannerImage ?? this.bannerImage,
      bannerTempImage: bannerTempImage ?? this.bannerTempImage,
      status: status ?? this.status,
      email: email ?? this.email,
      isBanned: isBanned ?? this.isBanned,
      isDealer: isDealer ?? this.isDealer,
      designation: designation ?? this.designation,
      address: address ?? this.address,
      phone: phone ?? this.phone,
      totalCar: totalCar ?? this.totalCar,
      profileState: profileState ?? this.profileState,
    );
  }

  Map<String, String> toMap() {
    return <String, String>{
      'id': id.toString(),
      'username': username,
      'name': name,
      'image': image,
      'banner_image': bannerImage,
      'status': status,
      'email': email,
      'is_banned': isBanned,
      'is_dealer': isDealer.toString(),
      'designation': designation,
      'address': address,
      'phone': phone,
      'total_car': totalCar.toString(),
    };
  }

  factory User.fromMap(Map<String, dynamic> map) {
    return User(
      id: map['id'] != null ? int.parse(map['id'].toString()) : 0,
      username: map['username'] ?? '',
      name: map['name'] ?? '',
      image: map['image'] ?? '',
      bannerImage: map['banner_image'] ?? '',
      status: map['status'] ?? '',
      email: map['email'] ?? '',
      isBanned: map['is_banned'] ?? '',
      isDealer: map['is_dealer'] != null ? int.parse(map['is_dealer'].toString()) : 0,
      designation: map['designation'] ?? '',
      address: map['address'] ?? '',
      phone: map['phone'] ?? '',
      totalCar: map['total_car'] != null ? int.parse(map['total_car'].toString()) : 0,
    );
  }

  static User init(){
    return const User(
        id: 0,
        username: '',
        name: '',
        image: '',
      tempImage: '',
      bannerImage: '',
      bannerTempImage: '',
        status: '',
      email: '',
        isBanned: '',
        isDealer: 0,
        designation: '',
        address: '',
        phone: '',
        totalCar: 0,
      profileState: ProfileInitial(),
    );
  }

  static User reset(){
    return const User(
      id: 0,
      username: '',
      name: '',
      image: '',
      tempImage: '',
      bannerImage: '',
      bannerTempImage: '',
      status: '',
      email: '',
      isBanned: '',
      isDealer: 0,
      designation: '',
      address: '',
      phone: '',
      totalCar: 0,
      profileState: ProfileInitial(),
    );
  }

  String toJson() => json.encode(toMap());

  factory User.fromJson(String source) => User.fromMap(json.decode(source) as Map<String, dynamic>);

  @override
  bool get stringify => true;

  @override
  List<Object> get props {
    return [
      id,
      username,
      name,
      image,
      tempImage,
      bannerImage,
      bannerTempImage,
      status,
      email,
      isBanned,
      isDealer,
      designation,
      address,
      phone,
      totalCar,
      profileState,
    ];
  }
}
