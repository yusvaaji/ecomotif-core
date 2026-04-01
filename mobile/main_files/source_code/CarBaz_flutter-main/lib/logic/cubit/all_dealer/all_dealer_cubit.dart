import 'package:ecomotif/data/model/home/home_model.dart';
import 'package:ecomotif/data/model/search_attribute/search_attribute_model.dart';
import 'package:ecomotif/logic/bloc/login/login_bloc.dart';
import 'package:ecomotif/logic/cubit/all_dealer/all_dealer_state.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../data/data_provider/remote_url.dart';
import '../../../utils/utils.dart';
import '../../repository/home_repository.dart';
import 'dealer_search_state_model.dart';


class AllDealerCubit extends Cubit<DealerSearchStateModel> {
  final HomeRepository _homeRepository;
  final LoginBloc _loginBloc;

  AllDealerCubit({
    required HomeRepository homeRepository,
    required LoginBloc loginBloc,
  })  : _homeRepository = homeRepository,
        _loginBloc = loginBloc,
        super(DealerSearchStateModel.init());


  CityModel? cityModel;

  List<Dealers> allDealerModel = [];

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



  Future<void> getAllDealersList() async {
    emit(state.copyWith(allDealerState: AllDealerStateLoading()));
    final uri = Utils.dealerWithCodeSearch(
      RemoteUrls.allDealer,
      state.initialPage.toString(),
      state.location,
      state.search,
      _loginBloc.state.languageCode,
    );

    print("dealer search url $uri");
    final result = await _homeRepository.getAllDealerList(uri);
    result.fold((failure) {
      final errorState = AllDealerStateError(failure.message, failure.statusCode);
      emit(state.copyWith(allDealerState: errorState));
    }, (success) {
      if (state.initialPage == 1) {
        allDealerModel = success;
        final loaded = AllDealerStateLoaded(allDealerModel);
        emit(state.copyWith(allDealerState: loaded));
      } else {
        allDealerModel.addAll(success);
        final loaded = AllDealerStateMoreLoaded(allDealerModel);
        emit(state.copyWith(allDealerState: loaded));
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
    allDealerModel = [];
    getAllDealersList();
  }

  void clearFilters() {
    emit(state.copyWith(
        location: '',
        search: '',
    ));
    applyFilters();
  }

  Future<void> getDealerSearch() async {
    emit(state.copyWith(allDealerState: GetDealerCityStateLoading()));
    final uri = Utils.tokenWithCode(
      RemoteUrls.getDealerCity,
      _loginBloc.userInformation!.accessToken,
      _loginBloc.state.languageCode,
    );

    final result = await _homeRepository.getDealerCity(uri);
    result.fold((failure) {
      final errorState =
      GetDealerCityStateError(failure.message, failure.statusCode);
      emit(state.copyWith(allDealerState: errorState));
    }, (success) {
      cityModel = success;
      final successState = GetDealerCityStateLoaded(success);
      emit(state.copyWith(allDealerState: successState));
    });
  }


  void initPage() {
    emit(state.copyWith(initialPage: 1, isListEmpty: false));
  }
}
