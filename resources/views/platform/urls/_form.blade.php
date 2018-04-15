<div class="form-group row">
    <label for="inputUrl" class="col-sm-2 col-form-label">Short URL</label>
    <div class="col-sm-10 form-row">
        <div class="col-auto">
            <select class="custom-select" autofocus>
                <option selected>https://vats.im/</option>
            </select>
        </div>
        <div class="col">
            <input type="text" class="form-control{{ $errors->has('url') ? ' is-invalid' : '' }}"
                   id="inputUrl" name="url" value="{{ old('url') ?: $url->url }}"
                   placeholder="my-short-url" maxlength="250" required>
            @if ($errors->has('url'))
                <div class="invalid-feedback">
                    {{ $errors->first('url') }}
                </div>
            @endif
            <small class="form-text text-muted">
                The short form of the URL. Leave blank to have one automatically generated.
            </small>
        </div>
    </div>
</div>

<div class="form-group row">
    <label for="inputRedirectUrl" class="col-sm-2 col-form-label">Redirect URL</label>
    <div class="col-sm-10">
        <input type="text" class="form-control{{ $errors->has('redirect_url') ? ' is-invalid' : '' }}"
               id="inputRedirectUrl" name="redirect_url" value="{{ old('redirect_url') ?: $url->redirect_url }}"
               placeholder="https://example.com/redirect-here" required maxlength="1000">
        @if ($errors->has('redirect_url'))
            <div class="invalid-feedback">
                {{ $errors->first('redirect_url') }}
            </div>
        @endif
        <small class="form-text text-muted">
            The URL you want to redirect to.
        </small>
    </div>
</div>