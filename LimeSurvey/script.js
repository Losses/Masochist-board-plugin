/**
 * Created by Don on 3/18/2015.
 */

mKnowledge.registerPlugin('pluginPageCtrl', function ($scope, $rootScope, $http) {
        $scope.viewed = false;
        $scope.currentPage = 0;
        $scope.pageSize = 10;
        $scope.maxPage = 0;
        $scope.formData = {};
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
            for (var k = 0; k < $scope.required.length; k++) {
                if ($scope.formData[$scope.required[k]] === undefined) {
                    publicWarning('问卷中有未完成的题目！');
                    break;
                }
            }
        };


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
                    console.log(response.question[key].required);

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
)
;