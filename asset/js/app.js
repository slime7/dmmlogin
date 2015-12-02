angular.module('dmmLogin', ['angular-loading-bar'])

        .config(['cfpLoadingBarProvider', function (cfpLoadingBarProvider) {
            cfpLoadingBarProvider.includeSpinner = false;
          }])

        .controller('mainCtrl', [
          '$scope', '$http',
          function ($scope, $http) {
            $scope.login_data = {
              email: !!si_string ? si_string.email : '',
              password: !!si_string ? si_string.password : '',
              remember: false,
              loadType: 'iframe'
            };
            $scope.hasCookie = !!$scope.login_data.email;
            $scope.error = '';

            $scope.login = function (cookie) {
              $scope.login_data.action = !!cookie ? 'usecookie' : 'login';
              var req = {
                method: 'POST',
                url: '/ajax.php',
                data: $scope.login_data
              };

              $http(req).then(
                      function (response) {
                        //$scope.loginContent = response;
                        if ( response.status !== 200 ) {
                          $scope.error = 'network error';
                        } else {
                          var json = response.data;
                          if ( !json.success ) {
                            $scope.error = json.msg;
                          } else {
                            //$location.url(json.data.link);
                            location.href = '/';
                          }
                        }
                      },
                      function () {
                        $scope.error = 'network error';
                      });
            };
          }]);


