import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    
    static targets = ['ruestzeit'];

    connect() {
        this.element.addEventListener('quill:connect', (event) => { this._onConnect(event) });
    }

    disconnect() {
        this.element.removeEventListener('quill:connect', this._onConnect);
    }

    _onConnect(event) {
        // The quill has been created
        console.log(this.ruestzeitTarget)
        const ruestzeitTitle = this.ruestzeitTarget.value;
        
        window.quill = event.detail;
        window.quill.setText("Hallo (vorname),\n\nHier deinen Inhalt der E-Mail einfügen!\n\nViele Grüße,\nDein Team der " + ruestzeitTitle)
        // console.log(event.detail); // You can access the quill instance using the event detail
        // alert("onConnect")
        // let quill = event.detail;
        // // e.g : if you want to add a new keyboard binding
        // quill.keyboard.addBinding({
        //     key: 'b',
        //     shortKey: true
        // }, function(range, context) {
        //     this.quill.formatText(range, 'bold', true);
        // });
          
        // // e.g if you want to add a custom clipboard
        // quill.clipboard.addMatcher(Node.TEXT_NODE, (node, delta) => {
        //     return new Delta().insert(node.data);
        // });
    }
}
