import 'package:ecomotif/data/model/home/home_model.dart';
import 'package:ecomotif/data/model/search_attribute/search_attribute_model.dart';
import 'package:ecomotif/logic/bloc/login/login_bloc.dart';
import 'package:ecomotif/logic/cubit/all_cars/all_cars_state.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../data/data_provider/remote_url.dart';
import '../../../utils/utils.dart';
import '../../repository/home_repository.dart';
import 'all_car_state_model.dart';

class AllCarsCubit extends Cubit<CarSearchStateModel> {
  final HomeRepository _homeRepository;
  final LoginBloc _loginBloc;

  AllCarsCubit({
    required HomeRepository homeRepository,
    required LoginBloc loginBloc,
  })  : _homeRepository = homeRepository,
        _loginBloc = loginBloc,
        super(CarSearchStateModel.init());

  List<FeaturedCars> allCarsModel = [];

  SearchAttributeModel? searchAttributeModel;

  CityModel? cityModel;

  void keyChange(String text) {
    emit(state.copyWith(
      search: text,
    ));
    print("key: ${state.search}");
  }

  void locationChange(String text) {
    emit(state.copyWith(
      location: text,
    ));
  }

  void countryChange(String text) {
    emit(state.copyWith(
      countryId: text,
    ));
  }

  void brandChange(String text) {
    emit(state.copyWith(
      brands: text,
    ));
  }

  void conditionChange(List<String> selectedColor) {
    emit(state.copyWith(
      condition: selectedColor,
    ));
  }

  void purposeChange(List<String> selectedPurpose) {
    emit(state.copyWith(
      purpose: selectedPurpose,
    ));
  }

  void featureChange(List<String> selectedFeature) {
    emit(state.copyWith(
      feature: selectedFeature,
    ));
  }

  Future<void> getAllCarsList() async {
    emit(state.copyWith(allCarsState: AllCarsStateLoading()));
    final uri = Utils.tokenWithCodeSearch(
      RemoteUrls.allCarList,
      state.initialPage.toString(),
      state.brands.toString(),
      state.location,
      state.countryId,
      state.search,
      _loginBloc.state.languageCode,
      state.feature,
      state.purpose,
      state.condition,
    );

    print("search url $uri");
    final result = await _homeRepository.getAllCarList(uri);
    result.fold((failure) {
      final errorState = AllCarsStateError(failure.message, failure.statusCode);
      emit(state.copyWith(allCarsState: errorState));
    }, (success) {
      if (state.initialPage == 1) {
        allCarsModel = success;
        final loaded = AllCarsStateLoaded(allCarsModel);
        emit(state.copyWith(allCarsState: loaded));
      } else {
        allCarsModel.addAll(success);
        final loaded = AllCarsStateMoreLoaded(allCarsModel);
        emit(state.copyWith(allCarsState: loaded));
      }
      state.initialPage++;
      if (success.isEmpty && state.initialPage != 1) {
        emit(state.copyWith(isListEmpty: true));
      }
    });
  }

  Future<void> applyFilters() async {
    // Apply filters to the existing event list
    state.initialPage = 1;
    allCarsModel = [];
    getAllCarsList();
  }

  void clearFilters() {
    emit(state.copyWith(
        location: '',
        countryId: '',
        brands: '',
        search: '',
        feature: [],
        purpose: [],
        condition: []));
    applyFilters();
  }

  Future<void> getSearchAttribute() async {
    emit(state.copyWith(allCarsState: GetSearchAttributeStateLoading()));
    final uri = Utils.onlyCode(
      RemoteUrls.getSearchAttribute,
      _loginBloc.state.languageCode,
    );

    final result = await _homeRepository.getSearchAttribute(uri);
    result.fold((failure) {
      final errorState =
          GetSearchAttributeStateError(failure.message, failure.statusCode);
      emit(state.copyWith(allCarsState: errorState));
    }, (success) {
      searchAttributeModel = success;
      final successState = GetSearchAttributeStateLoaded(success);
      emit(state.copyWith(allCarsState: successState));
    });
  }

  Future<void> getCity(String id) async {
    print("get city ");
    emit(state.copyWith(allCarsState: GetCityStateLoading()));
    final uri = Utils.tokenWithCode(
      RemoteUrls.getCity(id),
      _loginBloc.userInformation!.accessToken,
      _loginBloc.state.languageCode,
    );

    final result = await _homeRepository.getCity(uri, id);
    result.fold((failure) {
      final errorState = GetCityStateError(failure.message, failure.statusCode);
      emit(state.copyWith(allCarsState: errorState));
    }, (success) {
      cityModel = success;
      final successState = GetCityStateLoaded(success);
      emit(state.copyWith(allCarsState: successState));
    });
  }

  void initPage() {
    emit(state.copyWith(initialPage: 1, isListEmpty: false));
  }
}
