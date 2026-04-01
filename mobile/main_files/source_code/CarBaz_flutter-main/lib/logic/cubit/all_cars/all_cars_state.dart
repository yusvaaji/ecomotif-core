import 'package:ecomotif/data/model/home/home_model.dart';
import 'package:ecomotif/data/model/search_attribute/search_attribute_model.dart';
import 'package:equatable/equatable.dart';



abstract class AllCarsState extends Equatable {
  const AllCarsState();

  @override
  List<Object> get props => [];
}

class AllCarsInitial extends AllCarsState {
  const AllCarsInitial();

  @override
  List<Object> get props => [];
}

class AllCarsStateLoading extends AllCarsState {}

class AllCarsStateLoaded extends AllCarsState {
  final List<FeaturedCars> allCars;

  const AllCarsStateLoaded(this.allCars);

  @override
  List<Object> get props => [allCars];
}

class AllCarsStateMoreLoaded extends AllCarsState {
  final List<FeaturedCars> allCars;

  const AllCarsStateMoreLoaded(this.allCars);

  @override
  List<Object> get props => [allCars];
}

class AllCarsStateError extends AllCarsState {
  final String message;
  final int statusCode;

  const AllCarsStateError(this.message, this.statusCode);

  @override
  List<Object> get props => [message, statusCode];
}

/// Get Search Attribute

class GetSearchAttributeStateLoading extends AllCarsState {}

class GetSearchAttributeStateLoaded extends AllCarsState {
  final SearchAttributeModel searchAttributeModel;

  const GetSearchAttributeStateLoaded(this.searchAttributeModel);

  @override
  List<Object> get props => [searchAttributeModel];
}


class GetSearchAttributeStateError extends AllCarsState {
  final String message;
  final int statusCode;

  const GetSearchAttributeStateError(this.message, this.statusCode);

  @override
  List<Object> get props => [message, statusCode];
}


/// get City
class GetCityStateLoading extends AllCarsState {}

class GetCityStateLoaded extends AllCarsState {
  final CityModel city;

  const GetCityStateLoaded(this.city);

  @override
  List<Object> get props => [city];
}


class GetCityStateError extends AllCarsState {
  final String message;
  final int statusCode;

  const GetCityStateError(this.message, this.statusCode);

  @override
  List<Object> get props => [message, statusCode];
}