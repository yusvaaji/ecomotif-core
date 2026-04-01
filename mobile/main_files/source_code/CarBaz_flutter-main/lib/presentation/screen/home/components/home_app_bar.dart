import 'package:ecomotif/logic/bloc/login/login_bloc.dart';
import 'package:ecomotif/logic/cubit/compare/compare_list_cubit.dart';
import 'package:ecomotif/routes/route_names.dart';
import 'package:ecomotif/utils/k_images.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../data/data_provider/remote_url.dart';
import '../../../../logic/cubit/profile/profile_cubit.dart';
import '../../../../utils/constraints.dart';
import '../../../../utils/utils.dart';
import '../../../../widgets/circle_image.dart';
import '../../../../widgets/custom_image.dart';
import '../../../../widgets/custom_text.dart';

class HomeAppBar extends StatefulWidget {
  const HomeAppBar({super.key});

  @override
  State<HomeAppBar> createState() => _HomeAppBarState();
}

class _HomeAppBarState extends State<HomeAppBar> {
  late ProfileCubit profileData;
  late CompareCubit compareCubit;
  late LoginBloc loginBloc;
  late String image;
  late String name;
  late String email;

  @override
  void initState() {
    super.initState();
    _initState();
  }

  _initState() {
    profileData = context.read<ProfileCubit>();
    loginBloc = context.read<LoginBloc>();
    compareCubit = context.read<CompareCubit>();
    compareCubit.getCompareList();
    if (profileData.user != null && profileData.user!.image.isNotEmpty) {
      image = RemoteUrls.imageUrl(profileData.user!.image);
    } else {
      image = KImages.profileImage;
      // name = 'user';
      // email = 'user@gmail.com';
      //name = "User";
    }
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.sizeOf(context);
    // print('nameeeeeeeee: ${loginBloc.userInformation!.user!.name}');
    // print('nameeee222: $name');
    return SliverAppBar(
      automaticallyImplyLeading: false,
      toolbarHeight: Utils.vSize(130.0),
      backgroundColor: const Color(0xFF405FF2),
      pinned: true,
      flexibleSpace: Stack(
        fit: StackFit.loose,
        clipBehavior: Clip.none,
        children: [
          FlexibleSpaceBar(
            titlePadding: Utils.only(top: 55.0, left: 20.0, right: 20.0),
            title: Column(
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        GestureDetector(
                            onTap: () {
                              if (Utils.isLoggedIn(context)) {
                                Navigator.pushNamed(
                                    context, RouteNames.profileScreen);
                              } else {
                                Utils.showSnackBarWithLogin(context);
                              }
                            },
                            child: CircleImage(image: image, size: 56.0)),
                        Utils.horizontalSpace(8.0),
                        Column(
                          mainAxisSize: MainAxisSize.min,
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            CustomText(
                              maxLine: 2,
                              text: loginBloc.userInformation?.user?.name ??
                                  "Guest",
                              fontSize: 16.0,
                              color: whiteColor,
                              fontFamily: bold700,
                              // overflow: TextOverflow.ellipsis,
                            ),
                            Utils.verticalSpace(4.0),
                            const CustomText(
                              text: 'Welcome back !',
                              fontSize: 12.0,
                              color: whiteColor,
                            )
                          ],
                        ),
                      ],
                    ),
                    GestureDetector(
                      onTap: () {
                        if (Utils.isLoggedIn(context)) {
                          Navigator.pushNamed(
                              context, RouteNames.compareScreen);
                        } else {
                          Utils.showSnackBarWithLogin(context);
                        }
                      },
                      child: Stack(
                        clipBehavior: Clip.none,
                        children: [
                          Container(
                            decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                border: Border.all(
                                    color: whiteColor.withOpacity(0.5))),
                            child: Padding(
                              padding: Utils.all(value: 16.0),
                              child: const CustomImage(
                                path: KImages.compare,
                              ),
                            ),
                          ),
                          Positioned(
                              top: -10,
                              right: -4,
                              child: Container(
                                decoration: const BoxDecoration(
                                    shape: BoxShape.circle, color: whiteColor),
                                child: Padding(
                                  padding: Utils.all(value: 8.0),
                                  child: CustomText(
                                      text: compareCubit.compareListModel
                                              ?.compareList?.length
                                              .toString() ??
                                          '0'),
                                ),
                              ))
                        ],
                      ),
                    )
                  ],
                ),
              ],
            ),
          ),
          Positioned(
            bottom: -44.0,
            left: 20.0,
            right: 20.0,
            child: GestureDetector(
              onTap: () {
                Navigator.pushNamed(context, RouteNames.allCarScreen);
                // context.read<HomeCubit>().clearFilter();
                // Navigator.pushNamed(context, RouteNames.popularEventScreen);
                // final controller = MainController();
                // controller.naveListener.add(1);
              },
              child: Container(
                height: Utils.vSize(56.0),
                width: Utils.mediaQuery(context).width,
                margin: Utils.symmetric(v: 16.0, h: 0.0),
                padding:
                    Utils.only(left: 20.0, right: 6.0, top: 6.0, bottom: 6.0),
                decoration: BoxDecoration(
                    color: whiteColor,
                    borderRadius: Utils.borderRadius(),
                    boxShadow: [
                      BoxShadow(
                        color: const Color(0xff0000000).withOpacity(0.12),
                        blurRadius: 40.0,
                        offset: const Offset(0, 2),
                        spreadRadius: 0,
                      ),
                    ]),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // const CustomImage(path: KImages.search),
                        CustomText(
                          text: 'Search here...',
                          color: sTextColor,
                        ),
                      ],
                    ),
                    Container(
                        padding: Utils.all(value: 13.0),
                        decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(10.0),
                            color: const Color(0xFF0D274E)),
                        child: const CustomImage(
                          path: KImages.searchIcon,
                          height: 18.0,
                        )),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
