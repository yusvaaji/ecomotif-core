import 'package:ecomotif/logic/cubit/profile/profile_cubit.dart';
import 'package:ecomotif/logic/cubit/website_setup/website_setup/website_setup_cubit.dart';
import 'package:ecomotif/widgets/custom_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../../../utils/constraints.dart';
import '../../../../../utils/utils.dart';
import '../../../../data/data_provider/remote_url.dart';
import '../../../../data/model/auth/user_response_model.dart';
import '../../../../utils/k_images.dart';
import '../../../../widgets/circle_image.dart';


class ProfilePictureView extends StatelessWidget {
  const ProfilePictureView({super.key});

  @override
  Widget build(BuildContext context) {
    final pCubit = context.read<ProfileCubit>();
    final sCubit = context.read<WebsiteSetupCubit>();
    return BlocBuilder<ProfileCubit, User>(
      builder: (context, state) {
        final defaultImg = sCubit.setting != null &&
            sCubit.setting!.setting != null &&
            sCubit.setting!.setting!.defaultAvatar.isNotEmpty
            ? RemoteUrls.imageUrl(sCubit.setting!.setting!.defaultAvatar)
            : KImages.profileImage;

        final existImage = pCubit.state.tempImage.isNotEmpty
            ? RemoteUrls.imageUrl(pCubit.state.tempImage)
            : defaultImg;

        final stateImg = state.image.isNotEmpty ? state.image : existImage;
        debugPrint('state-image ${pCubit.state.image}');
        return Center(
          child: Stack(
            children: [
              CircleImage(
                  image: stateImg, size: 140.0, isFile: state.image.isNotEmpty),
              Positioned(
                right: 6.0,
                bottom: 5.0,
                child: GestureDetector(
                  onTap: () async {
                    final img = await Utils.pickSingleImage();
                    if (img != null && img.isNotEmpty) {
                      pCubit.imageChange(img);
                    }
                  },
                  child: const CircleAvatar(
                    maxRadius: 16.0,
                    backgroundColor: primaryColor,
                    child: Icon(
                      Icons.camera_alt_outlined,
                      color: whiteColor,
                      size: 20.0,
                    ),
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}



class ProfileBannerView extends StatelessWidget {
  const ProfileBannerView({super.key});

  @override
  Widget build(BuildContext context) {
    final pCubit = context.read<ProfileCubit>();
    final sCubit = context.read<WebsiteSetupCubit>();
    return BlocBuilder<ProfileCubit, User>(
      builder: (context, state) {
        // final defaultImg = sCubit.setting != null &&
        //     sCubit.setting!.setting != null &&
        //     sCubit.setting!.setting!.defaultAvatar.isNotEmpty
        //     ? RemoteUrls.imageUrl(sCubit.setting!.setting!.defaultAvatar)
        //     : KImages.bannerImage;


        final existImage = pCubit.state.bannerTempImage.isNotEmpty
            ? RemoteUrls.imageUrl(pCubit.state.bannerTempImage)
            : KImages.bannersImage;

        final stateImg = state.bannerImage.isNotEmpty ? state.bannerImage : existImage;
       // debugPrint('state-image ${pCubit.state.image}');
        return Center(
          child: Stack(
            children: [
              ClipRRect(
                borderRadius: BorderRadius.circular(10.0),
                child: CustomImage(
                  path: stateImg,
                  isFile: state.bannerImage.isNotEmpty,
                  height: 130,
                  width: double.infinity,
                  fit: BoxFit.cover,
                ),
              ),
              Positioned(
                right: 6.0,
                bottom: 5.0,
                child: GestureDetector(
                  onTap: () async {
                    final img = await Utils.pickSingleImage();
                    if (img != null && img.isNotEmpty) {
                      pCubit.bannerImageChange(img);
                    }
                  },
                  child: const CircleAvatar(
                    maxRadius: 16.0,
                    backgroundColor: primaryColor,
                    child: Icon(
                      Icons.camera_alt_outlined,
                      color: whiteColor,
                      size: 20.0,
                    ),
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}
