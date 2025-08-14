<div class="row g-3">
    <div class="col-lg-4">
        <div class="form-group">
            <label for="inp-cliente_id" class="required">Cliente</label>
            <div class="input-group">
                <select class="form-control select2" name="cliente_id" id="inp-cliente_id">
                    @foreach ($clientes as $item)
                    @endforeach
                </select>
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modal-cliente">
                    <i class="bx bx-plus"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="col-lg-12">

        <label for="" class="required">Descrição</label>
        <textarea class="form-control" name="descricao" id="" rows="3"></textarea>
    </div>
    <div class="mt-5">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>

