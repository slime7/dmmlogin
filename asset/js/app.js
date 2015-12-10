angular.module('dmmLogin', ['angular-loading-bar'])

  .config(['cfpLoadingBarProvider', function (cfpLoadingBarProvider) {
      cfpLoadingBarProvider.includeSpinner = false;
    }])

  .controller('mainCtrl', [
    '$scope', '$http', '$sce',
    function ($scope, $http, $sce) {
      $scope.login_data = {
        email: !!si_string ? si_string.email : '',
        password: !!si_string ? si_string.password : '',
        remember: false,
        loadType: 'iframe'
      };
      $scope.hasCookie = !!$scope.login_data.email;
      $scope.error = '';
      $scope.iframeLink = $sce.trustAsResourceUrl('');
      $scope.pageContent = 'login-frame.html';

      $scope.changePage = function (page) {
        $scope.pageContent = page;
      };

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
                if ( $scope.login_data.loadType == 'iframe' ) {
                  $scope.iframeLink = $sce.trustAsResourceUrl(json.data.link);
                  $scope.changePage('game-frame.html');
                } else if ( $scope.login_data.loadType == 'redirect' ) {
                  location.href = json.data.link;
                }
              }
            }
          },
          function () {
            $scope.error = 'network error';
          });
      };
    }]);


