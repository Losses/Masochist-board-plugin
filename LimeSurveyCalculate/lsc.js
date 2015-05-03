/**
 * Created by Don on 4/11/2015.
 */
mKnowledge.registerPlugin('pluginPageCtrl', function ($scope, $http, $rootScope) {
    $('title').text('LimeSurvey 数据分析器 - Masochist-board');

    $scope.dataQuery = {exportFrom: [], rFile: []};
    $scope.maxRangeId = 0;
    $scope.ranges = {
        'all': '全部信息',
        'answer': '答案',
        'mark': '分数'
    };

    $scope.addSource = function () {
        var pushContent = {
            'id': $scope.maxRangeId++,
            'identify': generateRandomCharacters(),
            'sheet': null,
            'type': 'all'
        };
        $scope.dataQuery.exportFrom.push(pushContent);

        setTimeout(function () {
            //sSelect('#' + pushContent.identify, $scope);
            //sSelect('#s_' + pushContent.identify, $scope);
        }, 100);
    };

    $http({
        method: 'POST',
        url: 'api/?plugin',
        data: $.param({
            'api': 'losses.lime.survey.calculate',
            'r_action': 'sheet_list'
        }),
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function (response) {
        $scope.surveyList = {};

        for (var i in response) {
            $scope.surveyList[response[i].location] = response[i];
        }
    });

    $scope.maxFileId = 0;

    $scope.addRFile = function () {
        var pushContent = {
            'id': $scope.maxFileId++,
            'identify': generateRandomCharacters(),
            'option': {}
        };
        $scope.dataQuery.rFile.push(pushContent);

        setTimeout(function () {
            sSelect('#' + pushContent.identify);
        }, 100);
    };

    $http({
        method: 'POST',
        url: 'api/?plugin',
        data: $.param({
            'api': 'losses.lime.survey.calculate',
            'r_action': 'script_list'
        }),
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function (response) {
        $scope.RFile = response;
    });

    $scope.debugInfo = function () {
        console.log($scope.dataQuery);
    };

    function generateRandomCharacters() {
        return md5(Date() + Math.random());
    }

    //SSELECT ANGULAR VERSION
    //QAQ 让我死！我要死！我不活啦！
    (function () {
        $rootScope.ASSelect = {elementInfo: {}};
        $rootScope.ASSelect.extendSelectBody = function (identify) {
            $rootScope.ASSelect.elementInfo[identify] = {};

            $rootScope.ASSelect.elementInfo[identify].selectStatus = 'selected';

            setTimeout(function () {
                $(document).one('click', function () {
                    $rootScope.ASSelect.elementInfo[identify].selectStatus = '';
                    $rootScope.$digest();
                });
            }, 200);
        };

        $rootScope.ASSelect.chooseSelection = function (identify, value) {
            $rootScope.ASSelect.elementInfo[identify].value = value;

            console.log($rootScope.ASSelect.elementInfo[identify]);
        }
    })();
});

mKnowledge.registerDirective('Option', function () {
    return {
        template: 'aaaaaa'
    }
});

//Custom Tag
(function () {
    var CustomTag = document.registerElement('lsc-option');


})();