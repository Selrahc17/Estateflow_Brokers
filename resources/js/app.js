import './bootstrap';
import Alpine from 'alpinejs';
import imagePicker from './image-picker';
import philippineProvinceAndRegion from './ph-address';
import registerForm from './register-form';

window.Alpine = Alpine;
window.philippineProvinceAndRegion = philippineProvinceAndRegion;
Alpine.data('imagePicker', imagePicker);
Alpine.data('registerForm', registerForm);
Alpine.start();
