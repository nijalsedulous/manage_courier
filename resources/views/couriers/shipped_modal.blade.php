<div id="shippedModal" class="modal-block mfp-hide">
    <section class="panel">
        <header class="panel-heading">
            <h2 class="panel-title">Courier Shipped Process</h2>
        </header>
        <div class="panel-body">

            <div class="form-group mt-lg">
                <label class="col-sm-3 control-label">Dispatch Through</label>
                <div class="col-sm-9">
                    <select class="form-control" v-model="selectedCourier.dispatch_through" >
                        <option>Select Dispatch</option>
                        <option v-for="(cc, key) in cc_master" :value="key">@{{cc}}</option>

                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label">Expected Delivery Date</label>
                <div class="col-sm-9">
                    <date-picker v-model="selectedCourier.delivery_date" :config="{format: 'MM/DD/YYYY'}"></date-picker>

                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label">Tracking Number</label>
                <div class="col-sm-9">
                    <input type="text"  class="form-control" v-model="selectedCourier.tracking_number" />
                </div>
            </div>

        </div>
        <footer class="panel-footer">
            <div class="row">
                <div class="col-md-12 text-right">
                    <button class="btn btn-primary" @click="savePayment">Save</button>
                    <button class="btn btn-default modal-dismiss">Cancel</button>
                </div>
            </div>
        </footer>
    </section>
</div>
