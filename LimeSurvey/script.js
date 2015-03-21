/**
 * Created by Don on 3/18/2015.
 */

mKnowledge.registerPlugin('pluginPageCtrl', function ($scope, $rootScope, $http) {
        $scope.route = $rootScope.routeSplited[1];

        $scope.viewed = false;
        $scope.currentPage = 0;
        $scope.pageSize = 10;
        $scope.maxPage = 0;
        $scope.formData = {};
        $scope.finished = false;
        $scope.switchPage = function (action) {
            if (action > 0) {
                if ($scope.currentPage + action >= $scope.maxPage) {
                    return false;
                }
            } else if (action < 0) {
                if ($scope.currentPage + action < 0) {
                    return false;
                }
            }

            $scope.currentPage += action;
            return true;
        };

        $scope.submitForm = function () {
            $scope.loading = true;
            switchLoading(true);

            for (var k = 0; k < $scope.required.length; k++) {
                if ($scope.formData[$scope.required[k]] === undefined) {
                    publicWarning('问卷中有未完成的题目！');
                    return false;
                }
            }

            $http({
                method: 'POST',
                url: 'api/?plugin',
                data: $.param({
                    'api': 'losses.lime.survey',
                    'sheet': $rootScope.routeSplited[1],
                    'action': 'submit',
                    'form_data': JSON.stringify($scope.formData)
                }),
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).success(function (response) {
                switchLoading(false);

                if (!response.code) {
                    $scope.loading = false;
                    publicWarning(response);
                }

                if (response.code !== 200) {
                    $scope.loading = false;
                    publicWarning(response.message);
                }
                else {
                    $scope.finished = true;
                    $scope.finishNotice = response.message;
                }
            });
        };

        $scope.getSid = function (sid) {
            return parseInt(sid.match(/[0-9]+/));
        };

        if ($scope.route === '' /**/
            || $scope.route === undefined) {
            $http({
                method: 'POST',
                url: 'api/?plugin',
                data: $.param({
                    'api': 'losses.lime.survey',
                    'list': ''
                }),
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).success(function (response) {
                $scope.surveyList = [];
                for (var i = 0; i < response.length; i++) {
                    $scope.surveyList.push({
                        'location': '#/' + $rootScope.routeSplited[0] + '/' + response[i].location,
                        'name':response[i].name
                    })
                }
            });
        } else {
            $http({
                method: 'POST',
                url: 'api/?plugin',
                data: $.param({
                    'api': 'losses.lime.survey',
                    'sheet': $rootScope.routeSplited[1],
                    'action': 'get'
                }),
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).success(function (response) {
                $scope.title = response.sheet_info.title;
                $scope.guidePage = response.sheet_info.guidePage;
                $scope.pageSize = response.sheet_info.questionPerPage;
                $scope.maxPage = Math.ceil(Object.keys(response.question).length / $scope.pageSize);
                $scope.required = [];

                for (var key in response.question) {
                    if (response.question[key].required === undefined /*nxt line*/
                        || response.question[key].required === true) {
                        $scope.required.push(key);
                    }
                }

                if (response.sheet_info) {
                    $scope.sheetInfo = response.sheet_info;
                }

                if (response.question) {
                    $scope.question = response.question;
                }
            });
        }
    }
)
;