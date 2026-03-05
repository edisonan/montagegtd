@extends('layouts.app')

<!-- Main Content -->
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8  mx-auto">
                <div class="card">
                    <div class="card-header">Reset Password</div>
                    <div class="card-body">

                        <form class="form-horizontal" role="form" method="POST" action="javascript:void(0)" id="passwordEmailForm">
                            {!! csrf_field() !!}

                            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                <label class="col-md-4 control-label">E-Mail Address</label>

                                <div class="col-md-6">
                                    <input type="email" class="form-control" name="email" value="{{ old('email') }}">

                                    @if ($errors->has('email'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-6  mx-auto">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-btn fa-envelope"></i>Send Password Reset Link
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function() {
            var form = document.getElementById('passwordEmailForm');
            if (!form) return;

            form.addEventListener('submit', function(e) {
                if (!window.TaskApiClient || typeof window.TaskApiClient.request !== 'function') {
                    return;
                }

                e.preventDefault();
                var submitBtn = form.querySelector('button[type="submit"]');
                var originalText = submitBtn ? submitBtn.innerHTML : '';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fa fa-btn fa-spinner fa-spin"></i>Sending...';
                }

                window.TaskApiClient.request({
                    method: 'POST',
                    url: '/api/v2/auth/password/email',
                    body: {
                        email: form.querySelector('input[name="email"]').value
                    },
                    skipAuth: true
                }).then(function(resp) {
                    var msg = (resp && resp.data && resp.data.result && resp.data.result.message)
                        ? resp.data.result.message
                        : 'Password reset link sent.';
                    alert(msg);
                }).catch(function(error) {
                    var msg = (error && error.data && (error.data.msg || error.data.message))
                        ? (error.data.msg || error.data.message)
                        : '发送失败，请稍后重试';
                    alert(msg);
                }).finally(function() {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                });
            });
        })();
    </script>
@endsection
