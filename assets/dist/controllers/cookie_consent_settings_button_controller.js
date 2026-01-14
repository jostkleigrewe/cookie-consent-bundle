import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  open() {
    document.dispatchEvent(new CustomEvent('cookie-consent:open'));
  }
}
