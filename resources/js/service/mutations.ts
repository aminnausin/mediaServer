// export const useChangeSettings = () => {
//     const queryClient = useQueryClient()
//     return useMutation({
//         mutationFn: handleToggleSettings,
//         onError: () => {
//             toast.error('Failed to update settings.')
//         },
//         onSuccess: (data, variable) => {
//             //TODO: make the toast message more dynamic based on the variable.settings fields for instance
//             console.log(variable)
//             toast.success('Successfully updated settings.')
//         },
//         onSettled: async (data, error, variable) => {
//             // TODO: Find the ID to go along with the queryKey for invalidating
//             console.log(variable)
//             await queryClient.invalidateQueries({
//                 queryKey: ['current-user'],
//             })
//         },
//     })
// }

// export const handleToggleSettings = async (data: SettingToggleInput) => {
//     try {
//         const response = await axios.patch(`/account/settings/${data.accountId}/`, {
//             setting_name: data.setting_name,
//         })
//         return response.data
//     } catch (error) {
//         console.error('Failed to toggle settings', error)
//     }
// }
