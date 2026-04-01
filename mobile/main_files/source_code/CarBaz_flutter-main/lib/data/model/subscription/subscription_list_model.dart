// ignore_for_file: public_member_api_docs, sort_constructors_first
import 'dart:convert';

import 'package:equatable/equatable.dart';

class SubscriptionListModel extends Equatable {
  final int id;
  final String planName;
  final String planPrice;
  final String expirationDate;
  final int maxCar;
  final int featuredCar;
  final String status;
  final int serial;
  final String createdAt;
  final String updatedAt;
  const SubscriptionListModel({
    required this.id,
    required this.planName,
    required this.planPrice,
    required this.expirationDate,
    required this.maxCar,
    required this.featuredCar,
    required this.status,
    required this.serial,
    required this.createdAt,
    required this.updatedAt,
  });

  SubscriptionListModel copyWith({
    int? id,
    String? planName,
    String? planPrice,
    String? expirationDate,
    int? maxCar,
    int? featuredCar,
    String? status,
    int? serial,
    String? createdAt,
    String? updatedAt,
  }) {
    return SubscriptionListModel(
      id: id ?? this.id,
      planName: planName ?? this.planName,
      planPrice: planPrice ?? this.planPrice,
      expirationDate: expirationDate ?? this.expirationDate,
      maxCar: maxCar ?? this.maxCar,
      featuredCar: featuredCar ?? this.featuredCar,
      status: status ?? this.status,
      serial: serial ?? this.serial,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }

  Map<String, dynamic> toMap() {
    return <String, dynamic>{
      'id': id,
      'plan_name': planName,
      'plan_price': planPrice,
      'expiration_date': expirationDate,
      'max_car': maxCar,
      'featured_car': featuredCar,
      'status': status,
      'serial': serial,
      'created_at': createdAt,
      'updated_at': updatedAt,
    };
  }

  factory SubscriptionListModel.fromMap(Map<String, dynamic> map) {
    return SubscriptionListModel(
      id: map['id'] ?? 0,
      planName: map['plan_name'] ?? '',
      planPrice: map['plan_price'] ?? '',
      expirationDate: map['expiration_date'] ?? '',
      maxCar: map['max_car'] != null ? int.parse( map['max_car'].toString()) : 0,
      featuredCar: map['featured_car'] != null ? int.parse(map['featured_car'].toString()) : 0,
      status: map['status'] ?? '',
      serial: map['serial'] != null ? int.parse(map['serial'].toString()) : 0,
      createdAt: map['created_at'] ?? '',
      updatedAt: map['updated_at'] ?? '',
    );
  }

  String toJson() => json.encode(toMap());

  factory SubscriptionListModel.fromJson(String source) => SubscriptionListModel.fromMap(json.decode(source) as Map<String, dynamic>);

  @override
  bool get stringify => true;

  @override
  List<Object> get props {
    return [
      id,
      planName,
      planPrice,
      expirationDate,
      maxCar,
      featuredCar,
      status,
      serial,
      createdAt,
      updatedAt,
    ];
  }
}
