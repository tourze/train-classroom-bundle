<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * TrainClassroom Bundle配置
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('train_classroom');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('attendance')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enable_face_recognition')
                            ->defaultTrue()
                            ->info('是否启用人脸识别考勤')
                        ->end()
                        ->booleanNode('enable_fingerprint')
                            ->defaultFalse()
                            ->info('是否启用指纹考勤')
                        ->end()
                        ->booleanNode('enable_card_reader')
                            ->defaultTrue()
                            ->info('是否启用刷卡考勤')
                        ->end()
                        ->booleanNode('enable_qr_code')
                            ->defaultTrue()
                            ->info('是否启用二维码考勤')
                        ->end()
                        ->integerNode('sign_in_tolerance_minutes')
                            ->defaultValue(15)
                            ->min(0)
                            ->info('签到容忍时间（分钟）')
                        ->end()
                        ->integerNode('sign_out_tolerance_minutes')
                            ->defaultValue(15)
                            ->min(0)
                            ->info('签退容忍时间（分钟）')
                        ->end()
                        ->booleanNode('allow_makeup_attendance')
                            ->defaultTrue()
                            ->info('是否允许补录考勤')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('schedule')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('default_schedule_duration_minutes')
                            ->defaultValue(120)
                            ->min(30)
                            ->info('默认排课时长（分钟）')
                        ->end()
                        ->integerNode('min_break_between_schedules_minutes')
                            ->defaultValue(15)
                            ->min(0)
                            ->info('排课间最小间隔（分钟）')
                        ->end()
                        ->booleanNode('allow_overlapping_schedules')
                            ->defaultFalse()
                            ->info('是否允许重叠排课')
                        ->end()
                        ->integerNode('max_advance_booking_days')
                            ->defaultValue(90)
                            ->min(1)
                            ->info('最大提前预约天数')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('classroom')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enable_monitoring')
                            ->defaultTrue()
                            ->info('是否启用教室监控')
                        ->end()
                        ->booleanNode('enable_environment_monitoring')
                            ->defaultFalse()
                            ->info('是否启用环境监控（温度、湿度等）')
                        ->end()
                        ->arrayNode('required_features')
                            ->scalarPrototype()->end()
                            ->defaultValue(['projector', 'whiteboard'])
                            ->info('教室必需设施')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('notification')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enable_email_notifications')
                            ->defaultTrue()
                            ->info('是否启用邮件通知')
                        ->end()
                        ->booleanNode('enable_sms_notifications')
                            ->defaultFalse()
                            ->info('是否启用短信通知')
                        ->end()
                        ->booleanNode('enable_wechat_notifications')
                            ->defaultTrue()
                            ->info('是否启用微信通知')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('archive')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('attendance_retention_days')
                            ->defaultValue(1095) // 3年
                            ->min(365)
                            ->info('考勤记录保留天数')
                        ->end()
                        ->integerNode('video_retention_days')
                            ->defaultValue(365) // 1年
                            ->min(90)
                            ->info('视频记录保留天数')
                        ->end()
                        ->booleanNode('enable_auto_cleanup')
                            ->defaultTrue()
                            ->info('是否启用自动清理')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
} 