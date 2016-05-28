angular.module('dmmLogin', ['angular-loading-bar'])

  .config(['cfpLoadingBarProvider', function (cfpLoadingBarProvider) {
    cfpLoadingBarProvider.includeSpinner = false;
  }])

  .directive('embedSrc', function () {
    return {
      restrict: 'A',
      link: function (scope, element, attrs) {
        var current = element;
        scope.$watch(function () {
          return attrs.embedSrc;
        }, function () {
          var clone = element
            .clone()
            .attr('src', attrs.embedSrc);
          current.replaceWith(clone);
          current = clone;
        });
      }
    };
  })

  .controller('mainCtrl', [
    '$scope', '$http', '$sce',
    function ($scope, $http, $sce) {
      var init_data = angular.fromJson(document.querySelector('#init-data').value);
      $scope.login_data = {
        email: !!init_data ? init_data.email : '',
        password: '',
        remember: false,
        loadType: 'include'
      };
      $scope.hasCookie = !!$scope.login_data.email;
      $scope.error = '';
      $scope.logining = false;
      $scope.flashLink = $sce.trustAsResourceUrl('');
      $scope.flashBase = $sce.trustAsResourceUrl('');
      $scope.gameloaded = false;

      $scope.login = function (cookie) {
        $scope.responseData = '';
        $scope.error = '';
        $scope.logining = true;
        $scope.login_data.action = !!cookie ? 'usecookie' : 'login';
        var req = {
          method: 'POST',
          url: '/ajax.php',
          data: $scope.login_data
        };

        $http(req).then(
          function (response) {
            $scope.responseData = response.data;
            $scope.logining = false;
            if (response.status !== 200) {
              $scope.error = 'network error';
            } else {
              var json = response.data;
              if (!json.success) {
                $scope.error = json.msg;
              } else {
                $scope.login_data.password = '';
                if ($scope.login_data.loadType == 'include') {
                  $scope.flashLink = $sce.trustAsResourceUrl(json.data.flash);
                  $scope.flashBase = $sce.trustAsResourceUrl(json.data.flash_base);
                  $scope.gameloaded = true;
                } else if ($scope.login_data.loadType == 'redirect') {
                  $scope.redirectModalClass = true;
                  location.href = json.data.flash;
                } else if ($scope.login_data.loadType == 'redirect2') {
                  $scope.redirectModalClass = true;
                  location.href = json.data.link;
                }
              }
            }
          },
          function () {
            $scope.logining = false;
            $scope.error = 'network error';
          });
      };

      $scope.relogin = function () {
        $scope.gameloaded = false;
        $scope.flashLink = $sce.trustAsResourceUrl('');
        $scope.flashBase = $sce.trustAsResourceUrl('');
      };
    }]);


