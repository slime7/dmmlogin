(function (angular) {
  'use strict';
  angular.module('dmmLogin', ['angular-loading-bar', 'ngAnimate', 'ngMaterial'])

    .config(['cfpLoadingBarProvider', function (cfpLoadingBarProvider) {
      cfpLoadingBarProvider.includeSpinner = false;
    }])

    .config(function ($mdGestureProvider) {

      // For mobile devices without jQuery loaded, do not
      // intercept click events during the capture phase.
      $mdGestureProvider.skipClickHijack();

    })

    .directive('embedSrc', function () {
      return {
        restrict: 'A',
        link: function (scope, element, attrs) {
          var current = element;
          scope.$watch(function () {
            return attrs.embedSrc;
          }, function () {
            var clone = element.clone()
              .attr('src', attrs.embedSrc);
            current.replaceWith(clone);
            current = clone;
          });
        }
      };
    })

    .controller('mainCtrl', [
      '$scope', '$http', '$sce', '$mdPanel', '$mdToast',
      function ($scope, $http, $sce, $mdPanel, $mdToast) {
        var init_data = angular.fromJson(document.querySelector('#init-data').value);
        $scope.login_data = {
          email: !!init_data ? init_data.email : '',
          password: '',
          remember: false,
          loadType: 'redirect'
        };
        $scope.hasCookie = !!$scope.login_data.email;
        $scope.logining = false;
        $scope.flashLink = $sce.trustAsResourceUrl('');
        $scope.flashBase = $sce.trustAsResourceUrl('');
        $scope.gameloaded = false;

        $scope.login = function (cookie) {
          $scope.responseData = '';
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
                showError('network error');
              } else {
                var json = response.data;
                if (!json.success) {
                  showError(json.msg);
                } else {
                  $scope.login_data.password = '';
                  if ($scope.login_data.loadType == 'include') {
                    $scope.flashLink = $sce.trustAsResourceUrl(json.data.flash);
                    $scope.flashBase = $sce.trustAsResourceUrl(json.data.flash_base);
                    $scope.gameloaded = true;
                  } else if ($scope.login_data.loadType == 'redirect') {
                    showRedirecting();
                    location.href = json.data.flash;
                  } else if ($scope.login_data.loadType == 'redirect2') {
                    showRedirecting();
                    location.href = json.data.link;
                  }
                }
              }
            },
            function () {
              $scope.logining = false;
              showError('network error');
            });
        };

        $scope.relogin = function () {
          $scope.gameloaded = false;
          $scope.flashLink = $sce.trustAsResourceUrl('');
          $scope.flashBase = $sce.trustAsResourceUrl('');
        };

        var showRedirecting = function () {
          var panel = $mdPanel;
          var position = panel.newPanelPosition().absolute().center();
          var config = {
            attachTo: angular.element(document.body),
            templateUrl: 'panel.tmpl.html',
            position: position,
            trapFocus: true,
            zIndex: 150,
            clickOutsideToClose: false,
            clickEscapeToClose: false,
            hasBackdrop: true,
            disableParentScroll: true
          };
          panel.open(config);
        };

        var showError = function (err) {
          $mdToast.show(
            $mdToast.simple()
              .textContent(err)
              .position('bottom left')
              .hideDelay(3000)
          );
        };
      }]);
})(angular)

