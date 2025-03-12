import { useState } from 'react'
import Profile from '../Profile/Profile'
import SettingsButton from '../SettingsButton/SettingsButton'
//@ts-ignore
import classes from './SettingsList.module.css'

export function SettingsList() {
	const [profileOpen, setProfileOpen] = useState(false)
	const [settingsOpen, setSettingsOpen] = useState(false)
	const [logoutOpen, setLogoutOpen] = useState(false)

	return (
		<div className={classes.SettingsList}>
			<SettingsButton
				title='Профиль'
				onClick={() => {
					setProfileOpen(!profileOpen)
				}}
			/>
			<SettingsButton
				title='Настройки'
				onClick={() => {
					setSettingsOpen(!settingsOpen)
				}}
			/>
			<SettingsButton
				title='Выход'
				onClick={() => {
					setLogoutOpen(!logoutOpen)
				}}
			/>

			{profileOpen && (
				<div className={classes.Modal}>
					<div className={classes.ModalContent}>
						<Profile
							nickname='nopox'
							onCancelChanges={() => setProfileOpen(false)}
							onSubmitChanges={(e: any) => {
								e.preventDefault()
								setProfileOpen(false)
							}}
						/>
					</div>
				</div>
			)}
		</div>
	)
}

export default SettingsList
