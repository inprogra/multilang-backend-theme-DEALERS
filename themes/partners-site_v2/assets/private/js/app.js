import volvoLogo from '../img/volvo-logo.svg';

import facebookIcon from '../img/facebook.svg';
import instagramIcon from '../img/instagram.svg';
import linkedinIcon from '../img/linkedin.svg';
import youtubeIcon from '../img/youtube.svg';
import twitterIcon from '../img/twitter.svg';

import errorIcon from '../img/errorIcon.svg';
import warningIcon from '../img/warningIcon.svg';
import successIcon from '../img/successIcon.svg';

import tooltipIcon from '../img/tooltip-icon.svg';
import checkmarkIcon from '../img/checkmark.svg';
import compassIcon from '../img/compass.svg';
import chevronIcon from '../img/chevron.svg';
import playIcon from '../img/play.svg';
import close_x from '../img/close_x.svg';
import batteryWithPlug from '../img/battery-with-plug.svg';

import formThanksImage from '../img/formThanksImage.png';
import pinImage from '../img/pin.png';

import previewComponentScreenshot from '../img/acf-blocks-previews/previewComponent.png';
import siteHeadingScreenshot from '../img/acf-blocks-previews/siteHeading.png';
import offerBoxesScreenshot from '../img/acf-blocks-previews/offerBoxes.png';
import offerCardsScreenshot from '../img/acf-blocks-previews/offerCards.png';
import twoColumnContentComponentScreenshot from '../img/acf-blocks-previews/twoColumnContentComponent.png';
import bannerWithContentOverlayScreenshot from '../img/acf-blocks-previews/bannerWithContentOverlay.png';
import heroImage from '../img/acf-blocks-previews/heroImage.png';
import gallery from '../img/acf-blocks-previews/gallery.png';
import textEditor from '../img/acf-blocks-previews/textEditor.png';
import tableEditor from '../img/acf-blocks-previews/tableEditor.png';
import textEditorExtended from '../img/acf-blocks-previews/textEditorExtended.png';

import notFoundImg from '../img/not-found-img.png';

import favicon16 from '../img/favicon-v2/favicon-16x16.v2.png';
import faviconSVG16 from '../img/favicon-v2/favicon-16x16.v2.svg';
import favicon32 from '../img/favicon-v2/favicon-32x32.v2.png';
import favicon180 from '../img/favicon-v2/favicon-180x180.v2.png';
import favicon192 from '../img/favicon-v2/favicon-192x192.v2.png';

import {importAll} from "./importAll";

const volvoIcons = importAll(require.context('../img/volvo-icons', false, /\.(png|jpe?g|svg)$/));

import './lazyloading';
import './scrollToSection';
import './fixText';
import './sticky';
import PerfectScrollbar from 'perfect-scrollbar';
import '../../../includes/views/components/atoms/input/input';
import '../../../includes/views/components/atoms/input-range/input-range';
import '../../../includes/views/components/atoms/checkbox/checkbox';
import '../../../includes/views/components/atoms/textarea/textarea';
import '../../../includes/views/components/atoms/select/select-multi';
import '../../../includes/views/components/atoms/tooltip/tooltip';
import '../../../includes/views/components/atoms/video/video';
import '../../../includes/views/components/atoms/map/map';


import '../../../includes/views/components/molecules/full-size-gallery/fullSizeGallery';
import '../../../includes/views/components/molecules/hero-slider/hero-slider';
import '../../../includes/views/components/molecules/car-gallery-slider/carGallerySlider';
import '../../../includes/views/components/molecules/two-column-list/two-column-list';

import '../../../includes/views/components/organisms/header/header';
import '../../../includes/views/components/organisms/form/form';
import '../../../includes/views/components/organisms/form-test-drive/form-test-drive';
import '../../../includes/views/components/organisms/form-service/form-service';
import '../../../includes/views/components/organisms/accordion-section/accordionSection';
import '../../../includes/views/components/organisms/stock-cars-slider/stockCarsSlider';
import '../../../includes/views/components/organisms/gallery/gallery';
import '../../../includes/views/components/organisms/cookies/cookies';
import '../../../includes/views/components/organisms/blog-posts/blog-posts';


import '../../../includes/views/layouts/stock/stock';
import '../../../includes/views/components/atoms/showroom-filter/showroom-filter';
import '../../../includes/views/layouts/stock-car-single/stockCarSingle';
import '../../../includes/views/layouts/models/models';
import '../../../includes/views/layouts/model-single/modelSingle';
import '../../../includes/views/layouts/side-form/side-form';
import '../../../includes/views/layouts/contact/contact';
import '../../../includes/views/layouts/test-drive/test-drive';
import '../../../includes/views/layouts/service/service';
 